# Thin wrappers around Composer/Sail. Composer scripts are the source of truth.
.DEFAULT_GOAL := help
SAIL := ./vendor/bin/sail

.PHONY: help setup up down fresh test check fix shell

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

setup: ## First-time setup with Docker (Sail): install, env, key, migrate+seed, assets
	@test -f .env || cp .env.example .env
	docker run --rm -u "$$(id -u):$$(id -g)" -v "$$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
	$(SAIL) up -d
	$(SAIL) artisan key:generate
	$(SAIL) artisan migrate --seed --force
	$(SAIL) npm install
	$(SAIL) npm run build
	@echo "\n  App:     http://localhost\n  Mailpit: http://localhost:8025\n  Login:   admin@example.com / password\n"

up: ## Start containers
	$(SAIL) up -d

down: ## Stop containers
	$(SAIL) down

fresh: ## Drop all tables, migrate and seed
	$(SAIL) artisan migrate:fresh --seed

test: ## Run the test suite
	$(SAIL) composer test

check: ## Run every quality gate (same as CI)
	$(SAIL) composer check

fix: ## Auto-fix style (Rector, Pint, Prettier)
	$(SAIL) composer fix

shell: ## Open a shell in the app container
	$(SAIL) shell
