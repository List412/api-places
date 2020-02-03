# PLACES-API

docker and docker-compose -- easiest way to start application

run `docker-compose up -d` to start containers with php, mysql and nginx

cd to symfony app directory

`cd wwww`

install packages, create database and run migrations

`docker-compose exec php composer install`

`docker-compose exec php php bin/console doctrine:database:create`

`docker-compose exec php php bin/console doctrine:migrations:migrate`

## project structure
`hosts` and `hosts_debug` stored nginx configs

`logs` and `mysql` volumes for nginx logs and mysql db

`www` symfony application folder

## api endpoints
GET `/nearbyPlace?ip=123.123.123.123&range=1234` get nearby places for location specified by ip address in some range (km), by default user ip address will be tooken

GET `/cityPlaces?city=Пермь?limit=50&offset=0` get places in given city, by default will be used city that correspond to user ip address
 
GET `/place` get all places

GET `/place/{id}` get place by id
 
POST `/place` post data to create new place

`{
     "lat": "58.0004558",
     "lng": "56.2485885",
     "name": "ЦКПиО им. Горького",
     "description": "парк горького",
     "type": "park"
 }` 
 
 PUT `/place/{id}` modify place by id 
 
 `{
      "lat": "58.0004558",
      "lng": "56.2485885",
      "name": "ЦКПиО им. Горького",
      "description": "парк горького",
      "type": "park"
  }` 
  
  DELETE `/place/{id}` get rid of place by id
  



