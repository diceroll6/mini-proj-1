package main

import (
	"database/sql"
	"encoding/json" // Added for JSON marshalling
	"fmt"
	"log"
	"net/http"
	"sync" // Added for mutex
	"time"

	"github.com/gorilla/websocket"
	_ "modernc.org/sqlite"
)

// Global variable to track connected WebSocket clients
// and a mutex to protect concurrent access to it.
var (
	clients = make(map[*websocket.Conn]bool)
	mu      sync.Mutex // Mutex to protect the clients map
)

type Row struct {
	ID     int
	Status string
}

// SocketMessage defines the structure for JSON messages sent over WebSocket
type SocketMessage struct {
	ID      int    `json:"id"`
	Status  string `json:"status"`  // Current/new status of the item
	Message string `json:"message"` // Descriptive message about the event
}

var upgrader = websocket.Upgrader{
	CheckOrigin: func(r *http.Request) bool {
		return true // Allow connections from any origin
	},
}

// Helper function to broadcast messages to all clients
func broadcastMessage(messageType int, message []byte) {
	mu.Lock()         // Lock the mutex before accessing clients map
	defer mu.Unlock() // Ensure mutex is unlocked when function returns

	for client := range clients {
		err := client.WriteMessage(messageType, message)
		if err != nil {
			log.Println("write error to client, removing:", err)
			client.Close()          // Close the connection
			delete(clients, client) // Remove from map
		}
	}
}

func handleConnection(w http.ResponseWriter, r *http.Request) {
	conn, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		log.Println("upgrade:", err)
		return
	}
	defer conn.Close()

	// Add client to the map of connected clients
	mu.Lock()
	clients[conn] = true
	mu.Unlock()
	log.Println("Client connected. Total clients:", len(clients))

	// Handle incoming messages from client (optional, you can remove if not needed)
	// For this example, we'll just keep the connection alive.
	// If you want clients to send messages, you'll need a read loop.
	// For now, this loop just waits for the connection to close.
	for {
		// You can optionally read messages here if clients are supposed to send data
		// mt, message, err := conn.ReadMessage()
		// For now, just check if connection is alive or handle pings
		// Gorilla's default handler will respond to pings automatically.
		// If ReadMessage is not called, server won't respond to pings unless a PingHandler is set.
		// To keep it simple, if client sends a message, it will be logged and connection might break
		// if not handled properly. For a pure server-push model, this read loop might be minimal.
		if _, _, err := conn.NextReader(); err != nil { // This checks if the connection is closed
			log.Println("Client read error (likely disconnected):", err)
			break
		}
		// If you expect messages:
		// mt, message, err := conn.ReadMessage()
		// if err != nil {
		// 	log.Println("read:", err)
		// 	break
		// }
		// log.Printf("recv: %s from %s", message, conn.RemoteAddr())
		// // Example: Echo back to client
		// err = conn.WriteMessage(mt, message)
		// if err != nil {
		// 	log.Println("write:", err)
		// 	break
		// }
	}

	// Remove client from the map on disconnect
	mu.Lock()
	delete(clients, conn)
	mu.Unlock()
	log.Println("Client disconnected. Total clients:", len(clients))
}

func handleSqlChanges() { // Renamed for clarity
	db, err := sql.Open("sqlite", "../database/database.sqlite") // Ensure this path is correct
	if err != nil {
		log.Fatal("DB open error:", err)
	}
	defer db.Close()

	if err := db.Ping(); err != nil {
		log.Fatal("DB ping error:", err)
	}
	log.Println("Successfully connected to the database.")

	cache := make(map[int]string)
	log.Println("Starting SQL change listener...")

	for {
		current := make(map[int]string)
		changed := false

		rows, err := db.Query(`SELECT id, status FROM csv_uploads WHERE status IN ('pending', 'processing')`)
		if err != nil {
			log.Println("DB query error:", err)
			time.Sleep(5 * time.Second)
			continue
		}

		for rows.Next() {
			var id int
			var status string
			if err := rows.Scan(&id, &status); err != nil {
				log.Println("Scan error:", err)
				continue
			}
			current[id] = status

			prevStatus, exists := cache[id]
			if !exists {
				log.Printf("NEW: ID %d has status %s\n", id, status)
				jsonMsg := SocketMessage{
					ID:      id,
					Status:  status,
					Message: fmt.Sprintf("New item (ID: %d) added with status: %s.", id, status),
				}
				payload, _ := json.Marshal(jsonMsg) // Error handling for Marshal can be added
				broadcastMessage(websocket.TextMessage, payload)
				changed = true
			} else if prevStatus != status {
				log.Printf("CHANGED: ID %d changed from %s to %s\n", id, prevStatus, status)
				jsonMsg := SocketMessage{
					ID:      id,
					Status:  status, // The new status
					Message: fmt.Sprintf("Item (ID: %d) status changed from %s to %s.", id, prevStatus, status),
				}
				payload, _ := json.Marshal(jsonMsg)
				broadcastMessage(websocket.TextMessage, payload)
				changed = true
			}
		}
		if err := rows.Err(); err != nil {
			log.Println("Rows iteration error:", err)
		}
		rows.Close()

		for id, oldStatus := range cache {
			// 'current' contains items that are STILL 'pending' or 'processing' from the latest DB query
			// If an 'id' from 'cache' is NOT in 'current', it means its status
			// is no longer 'pending' or 'processing'.
			if _, stillPresent := current[id]; !stillPresent {
				// This row is no longer 'pending' or 'processing'.
				// Let's find out its NEW actual status from the database.
				var newStatus string
				// Query for the row's current status, whatever it may be now
				err := db.QueryRow(`SELECT status FROM csv_uploads WHERE id = ?`, id).Scan(&newStatus)

				var jsonMsg SocketMessage
				if err != nil {
					if err == sql.ErrNoRows {
						// The row was deleted entirely
						log.Printf("RESOLVED (DELETED): ID %d (was %s) is no longer in the table.\n", id, oldStatus)
						jsonMsg = SocketMessage{
							ID:      id,
							Status:  "REMOVED", // Custom status to indicate removal from tracking/DB
							Message: fmt.Sprintf("Item (ID: %d, was %s) has been removed or is no longer tracked.", id, oldStatus),
						}
					} else {
						// Some other error occurred trying to get the new status
						log.Println("Re-query error for resolved row:", err)
						jsonMsg = SocketMessage{
							ID:      id,
							Status:  "UNKNOWN_RESOLVED", // Status indicating an issue finding its final state
							Message: fmt.Sprintf("Error re-querying resolved item (ID: %d). Previous status was %s.", id, oldStatus),
						}
					}
				} else {
					// Successfully fetched the new status for the resolved item
					log.Printf("RESOLVED: ID %d (was %s) changed to new status: %s\n", id, oldStatus, newStatus)
					jsonMsg = SocketMessage{
						ID:      id,
						Status:  newStatus, // THIS IS THE NEW STATUS FROM THE DATABASE
						Message: fmt.Sprintf("Item (ID: %d, was %s) resolved to status: %s.", id, oldStatus, newStatus),
					}
				}
				payload, _ := json.Marshal(jsonMsg) // Consider proper error handling for Marshal
				broadcastMessage(websocket.TextMessage, payload)
				changed = true
			}
		}

		cache = current
		if changed {
			// mu.Lock() // Lock before accessing clients for len, or rely on broadcastMessage's internal locking
			// clientCount := len(clients)
			// mu.Unlock()
			// log.Printf("Cache updated. Current tracked items: %d. Broadcasting changes potentially to clients.", len(cache))
			// More concise logging if not needing client count here:
			log.Printf("Cache updated. Current tracked items: %d. Changes broadcasted.", len(cache))

		}
		time.Sleep(2 * time.Second)
	}
}

func main() {
	// Start the SQL change listener in a new goroutine
	go handleSqlChanges()

	http.HandleFunc("/ws", handleConnection)

	log.Println("WebSocket server starting on :8080/ws")
	err := http.ListenAndServe(":8080", nil)
	if err != nil {
		log.Fatal("ListenAndServe Error:", err)
	}
	// This part will not be reached if ListenAndServe is successful
}
