# Wrappers compose : depuis le renommage override -> dev, dev et prod sont des overlays
# explicites (plus d'auto-merge). Ces cibles évitent de retaper les -f.
DC_DEV  := docker compose -f docker-compose.yml -f docker-compose.dev.yml
DC_PROD := docker compose -f docker-compose.yml -f docker-compose.prod.yml
UID     := $(shell id -u)
GID     := $(shell id -g)

.PHONY: up down build init test logs prod-pull prod-up

## --- DEV ---
up:                ## Démarre la stack dev (build inclus)
	$(DC_DEV) up -d --build

down:
	$(DC_DEV) down

build:
	$(DC_DEV) build

# Premier run sur un clone frais : peuple le bind-mount (vendor + sqlite + migrations).
# `-u $(UID):$(GID)` => les fichiers créés dans le volume appartiennent à l'utilisateur hôte.
init: up
	$(DC_DEV) exec -u $(UID):$(GID) php composer install
	$(DC_DEV) exec -u $(UID):$(GID) php touch database/database.sqlite
	$(DC_DEV) exec -u $(UID):$(GID) php php artisan migrate --force

test:
	$(DC_DEV) exec php composer test

logs:
	$(DC_DEV) logs -f php nginx scheduler

## --- PROD (sur le VPS) ---
prod-pull:
	$(DC_PROD) pull

prod-up:
	$(DC_PROD) up -d
