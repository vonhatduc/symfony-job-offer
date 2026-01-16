# Makefile for Docker Symfony project

.PHONY: help build up down restart logs shell composer console db-create db-migrate db-reset jwt-keys

# Colors
GREEN := \033[0;32m
NC := \033[0m

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-15s$(NC) %s\n", $$1, $$2}'

build: ## Build Docker images
	docker compose build --no-cache

up: ## Start containers
	docker compose up -d

down: ## Stop containers
	docker compose down

restart: down up ## Restart containers

logs: ## View logs
	docker compose logs -f

logs-php: ## View PHP logs
	docker compose logs -f php

shell: ## Access PHP container shell
	docker compose exec php bash

composer: ## Run composer command (usage: make composer c="require package")
	docker compose exec php composer $(c)

console: ## Run Symfony console command (usage: make console c="cache:clear")
	docker compose exec php php bin/console $(c)

install: ## Install dependencies
	docker compose exec php composer install

cache-clear: ## Clear Symfony cache
	docker compose exec php php bin/console cache:clear

db-create: ## Create database
	docker compose exec php php bin/console doctrine:database:create --if-not-exists

db-migrate: ## Run migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

db-diff: ## Generate migration
	docker compose exec php php bin/console doctrine:migrations:diff

db-reset: ## Reset database (drop, create, migrate)
	docker compose exec php php bin/console doctrine:database:drop --force --if-exists
	docker compose exec php php bin/console doctrine:database:create
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

jwt-keys: ## Generate JWT keys
	docker compose exec php php bin/console lexik:jwt:generate-keypair --overwrite

app-setup: ## Run initial app setup (seed data)
	docker compose exec php php bin/console app:setup

test: ## Run PHPUnit tests
	docker compose exec -e DATABASE_URL="mysql://root:root@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4" php php bin/console --env=test doctrine:database:create --if-not-exists
	docker compose exec -e DATABASE_URL="mysql://root:root@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4" php php bin/console --env=test doctrine:migrations:migrate --no-interaction
	docker compose exec -e DATABASE_URL="mysql://root:root@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4" php php bin/phpunit

first-install: build up install db-create db-migrate jwt-keys app-setup ## First time setup (build, install, db, jwt, seed)

status: ## Show container status
	docker compose ps
