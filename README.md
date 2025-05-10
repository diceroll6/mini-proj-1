
# php setup
```txt
-> php version 8.2 - 8.4

set in php ini
post_max_size = 512M
upload_max_filesize = 512M

~~~ php extension required ~~~
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=exif      ; Must be after mbstring as it depends on it
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=pgsql

extension=soap
extension=sockets
extension=sqlite3
extension=zip

====
```
# initial commands
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan queue:work
```


# end

---
---


```txt
i've made a websocket alternative
follow instructions below to try the alternative

u will need golang on ur machine

++
cd websocket-server
go mod download
go mod verify
go run server.go
++

after the server.go is runnning,
go to upload.blade.php

comment out the startPolling() function call (line 168 and line 299)
uncomment the top part from line 107 - 154
then refresh the page
```