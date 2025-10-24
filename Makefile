.PHONY: help up down restart bash composer install ecs ecs-fix

help: ## Show help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start Docker containers
	docker compose up -d

down: ## Stop Docker containers
	docker compose down

restart: down up ## Restart Docker containers

bash: ## Connect to PHP container
	docker compose exec php bash

composer: ## Run composer command (usage: make composer CMD="install")
	docker compose exec php composer $(CMD)

install: ## Install composer dependencies
	docker compose exec php composer install

update: ## Update composer dependencies
	docker compose exec php composer update

test: ## Run all tests (ECS + PHPStan)
	docker compose exec php composer ecs
	docker compose exec php composer phpstan

ecs: ## Check code style
	docker compose exec php composer ecs

ecs-fix: ## Fix code style
	docker compose exec php composer ecs-fix

phpstan: ## Run PHPStan static analysis
	docker compose exec php composer phpstan

phpstan-baseline: ## Generate PHPStan baseline
	docker compose exec php composer phpstan-baseline

build: ## Rebuild Docker images
	docker compose build --no-cache

logs: ## Show container logs
	docker compose logs -f

ps: ## Show running containers
	docker compose ps
