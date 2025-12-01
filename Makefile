.PHONY: help up down build logs fresh test

help:
	@echo "Commands: up, down, build, logs, fresh, test"

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

logs:
	docker compose logs -f

fresh:
	docker compose exec backend php artisan migrate:fresh --seed

test:
	docker compose exec backend php artisan test
