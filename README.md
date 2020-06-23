# Yesplan-asana

## Installation

```sh
docker-compose up -d
docker-compose exec phpfpm composer install
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

## Usage

```sh
docker-compose exec phpfpm bin/console app:yesplan:get-events
```
```sh
docker-compose exec phpfpm bin/console app:yesplan:delete-old-events
```

```sh
docker-compose exec phpfpm bin/console app:asana:createCards
```

## Coding standards

```sh
docker-compose exec phpfpm composer coding-standards-check
```

```sh
docker-compose exec phpfpm composer coding-standards-apply
```
