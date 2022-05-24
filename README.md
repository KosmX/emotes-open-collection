# [Emotes-open-collection](emotes.kosmx.dev)
An Open Source website for [Emotecraft](https://github.com/KosmX/emotes) emotes  
And a University homework ;)

## Build/Deploy/Test

The whole stuff is containerized, use `docker compose up` to start the service.  
You'll need compose V2 or newer to build.  

Before it is ready, you need to prepare the DB. In dev env, the MariaDB docker port is exposed to `8006`, you can connect to that with some DB frontend.  
Or `docker exec -it emotes-open-collection-db-... bash` to open the docker CLI then `mysql -u root -p` to open the db root shell.  

And execute the `create_db.sql` script.  

You also need some OAuth token to test the login, save that in `src/gh.token` file  
Then type `localhost:8080` in your browser  

## Usage 
Just log-in or register with GitHub,
Use the emotes dropdown menu to upload emotes. The site will reject not emotecraft emote files.

Use the built-in search to search.

## Binary format
Until now, the binary format is not a well-known format, but it can store the icon and the data in one file.  
[What is it](https://github.com/KosmX/emotes/wiki/Emote-binary)  

Emotecraft can open those file since 2.0 builds, feel free to use those.  
