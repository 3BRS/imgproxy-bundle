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

test: ## Run all tests (PHPUnit + ECS + PHPStan + Deptrac)
	docker compose exec php vendor/bin/phpunit --no-coverage
	docker compose exec php composer ecs
	docker compose exec php composer phpstan
	@docker compose exec php bash -c 'if [ -f vendor/bin/deptrac ]; then composer deptrac; else echo "Deptrac skipped (requires PHP 8.1+)"; fi'

phpunit: ## Run PHPUnit tests
	docker compose exec php vendor/bin/phpunit --no-coverage

phpunit-coverage: ## Run PHPUnit with coverage report
	docker compose exec -e XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-html=var/coverage

audit: ## Check for security vulnerabilities (dev dependencies only)
	docker compose exec php composer audit

ecs: ## Check code style
	docker compose exec php composer ecs

ecs-fix: ## Fix code style
	docker compose exec php composer ecs-fix

phpstan: ## Run PHPStan static analysis
	docker compose exec php composer phpstan

phpstan-baseline: ## Generate PHPStan baseline
	docker compose exec php composer phpstan-baseline

deptrac: ## Run Deptrac architecture analysis (requires PHP 8.1+)
	docker compose exec php composer deptrac

rector: ## Check code for automated refactoring opportunities
	docker compose exec php composer rector

rector-fix: ## Apply automated refactorings
	docker compose exec php composer rector-fix

infection: ## Run mutation testing (requires PHP 8.1+ and infection package)
	docker compose exec -e XDEBUG_MODE=coverage php vendor/bin/infection --threads=4

build: ## Rebuild Docker images
	docker compose build --no-cache

logs: ## Show container logs
	docker compose logs -f

ps: ## Show running containers
	docker compose ps
