.PHONY: help up down build logs fresh test test-local test-coverage test-unit test-feature

help:
	@echo "Commands: up, down, build, logs, fresh, test, test-local, test-coverage, test-unit, test-feature"

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

# Docker tests
test:
	docker compose exec backend php artisan test

test-docker-coverage:
	docker compose exec backend sh -c 'XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage-report'

# Local tests (run from backend directory)
test-local:
	cd backend && DB_DATABASE=:memory: php artisan test

test-coverage:
	cd backend && XDEBUG_MODE=coverage DB_DATABASE=:memory: ./vendor/bin/phpunit --coverage-html coverage-report

test-unit:
	cd backend && DB_DATABASE=:memory: php artisan test --testsuite=Unit

test-feature:
	cd backend && DB_DATABASE=:memory: php artisan test --testsuite=Feature
