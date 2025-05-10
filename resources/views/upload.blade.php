<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
    <style>
        :root {
            --main-font: consolas, courior-new, monospace;
        }

        * {
            font-family: var(--main-font);
        }

        main {
            margin: 1rem 3rem;
        }

        .upload-field input {
            border: 1px solid black;
            width: 100%;
            height: 4rem;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .table-section {
            margin-top: 2rem;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 0.2rem;
        }

        .txt-err {
            color: red;
        }
    </style>
</head>

<body>
    <main>
        <section>
            <fieldset>
                <legend>upload csv</legend>
                <form id="upload-form" action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
                    
                    <div class="upload-field">
                        <label for="">upload file (drag to box)</label>
                        <input type="file" name="uploaded_file">
                    </div>
                    @error('uploaded_file')
                    <div class="txt-err">
                        {{ $message }}
                    </div>
                    @enderror
    
                    <button class="submit-button" type="submit">submit</button>
    
                    <div class="txt-err" id="err-messages" hidden>
                    </div>
                </form>
            </fieldset>
        </section>

        <section class="table-section">
            <table id="main-table">
                <thead>
                    <tr>
                        <th>time</th>
                        <th>filename</th>
                        <th>status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($csv_upload_list as $item)
                    <tr data-id="{{ $item->id }}" data-status="{{ $item->status }}">
                        <td>
                            {{ $item->created_at?->format('Y-m-d g:ia') }} <br>
                            ({{ $item->created_at?->diffForHumans() }})
                        </td>
                        <td>{{ $item->uploaded_filename }}</td>
                        <td>{{ $item->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM is fully parsed and ready!');

        // TEST SOCKET START
        // TEST SOCKET START


        // const ws = new WebSocket('ws://localhost:8080/ws');

        // ws.onopen = () => {
        //     console.log('Connected to WebSocket server!');
        //     // You can send a message to the server here if needed:
        //     ws.send('Hello Server!');
        // };

        // ws.onmessage = (event) => {
        //     console.log('Received message:', event.data);

        //     let json_data = JSON.parse(event.data);

        //     console.log(json_data);

        //     updateRowStatus_just_id(json_data.id, json_data.status);

        //     if (json_data.status == 'REMOVED')
        //     {
        //         deleteRow_just_id(json_data.id)
        //     }
        // };

        // ws.onerror = (error) => {
        //     console.error('WebSocket error:', error);
        // };

        // ws.onclose = (event) => {
        //     console.log('Disconnected from WebSocket server.', event);
        //      if (event.wasClean) {
        //         console.log(`Connection closed cleanly, code=${event.code}, reason=${event.reason}`);
        //     } else {
        //         console.error('Connection died');
        //     }
        // };

        // // Add a button to the page to close the connection (for demonstration purposes)
        // const closeButton = document.createElement('button');
        // closeButton.textContent = 'Close Connection';
        // closeButton.onclick = () => {
        //     if (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING) {
        //         ws.close();
        //     } else {
        //       console.log("connection is already closed")
        //     }

        // };
        // document.body.appendChild(closeButton);


        // TEST SOCKET END
        // TEST SOCKET END

        const table = document.getElementById('main-table');
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const status = row.dataset.status;
            const id = row.dataset.id;
            if (status === 'pending' || status === 'processing') {
                startPolling(id); // Pass only the id
            }
        });

        function startPolling(id) {

            const row = document.querySelector(`tr[data-id="${id}"]`);

            if (!row)
            {
                return;   
            }

            const intervalId = setInterval(function() {
                fetch(`{{ route('upload-row-info', '') }}/${id}`)
                .then(response => response.json())
                .then(data => {
                    
                    if (!row)
                    {
                        clearInterval(intervalId);
                        return;
                    }

                    if (data.status === 'completed')
                    {
                        clearInterval(intervalId);
                        updateRowStatus(row, 'completed');
                    }
                    else if (data.status === 'error')
                    {
                        clearInterval(intervalId);
                        deleteRow(row);
                    }
                    else if (data.status === 'processing')
                    {
                        updateRowStatus(row, 'processing');
                    }
                })
                .catch(error => {
                    console.error('Error polling:', error);
                });
            }, 3000);
        }

        function updateRowStatus(row, newStatus) {
            const statusCell = row.querySelector('td:nth-child(3)'); // Get the status cell.
            statusCell.textContent = newStatus; // Simple text update
            row.dataset.status = newStatus; // update the data-status attribute
        }

        function updateRowStatus_just_id(id, newStatus) {
            let row = document.querySelector(`tbody tr[data-id="${id}"]`);
            if (!row)
            {
                return;
            }

            const statusCell = row.querySelector('td:nth-child(3)'); // Get the status cell.
            statusCell.textContent = newStatus; // Simple text update
            row.dataset.status = newStatus; // update the data-status attribute
        }

        function deleteRow(row) {
            row.remove();
        }

        function deleteRow_just_id(id) {
            let row = document.querySelector(`tbody tr[data-id="${id}"]`);
            if (!row)
            {
                return;
            }
            row.remove();
        }

        let form = document.getElementById('upload-form');
        let submit_button = form.querySelector('button[type="submit"]');

        form.onsubmit = function(event) {
            event.preventDefault();

            // disable submit button
            submit_button.disabled = true;

            // hide error message
            document.getElementById('err-messages').hidden = true;

            let formData = new FormData(form);

            // https://muffinman.io/blog/uploading-files-using-fetch-multipart-form-data/
            fetch("{{ route('upload') }}", {
                method: 'POST',
                headers: {
                    'accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData,
            })
            .then(res => {
                if (!res.ok) {
                    throw res;
                }
                return res.json();
            })
            .then(resdata => {
                let row_data = resdata.row_data;
                console.log(row_data);
 
                prepend_row(row_data.id, row_data.time, row_data.time_human, row_data.uploaded_filename, row_data.status);
                startPolling(row_data.id);
            })
            .catch(async err => {
                let data = await err.json();
                console.error('Error:', data.message);

                document.getElementById('err-messages').textContent = data.message;
                document.getElementById('err-messages').hidden = false;
            })
            .finally(wat => {
                submit_button.disabled = false;
            })
        }

        function prepend_row(id, time, time_human, filename, status)
        {
            // Get a reference to the table
            let tableRef = document.getElementById('main-table');
    
            // Insert a row at beginning, after headers
            let newRow = tableRef.insertRow(1);
    
            // Set the data-id attribute on the new row
            newRow.setAttribute('data-id', id);
            newRow.setAttribute('data-status', status);
    
            // Use innerHTML with string interpolation
            newRow.innerHTML = `
                <td>${time}<br>(${time_human})</td>
                <td>${filename}</td>
                <td>${status}</td>
            `;
        }
    });
</script>

</html>