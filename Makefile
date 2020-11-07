-include ./.env
export

.PHONY: help

help: ## Print Help (this message) and exit
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ./.env ## Build the containers
	@docker-compose up -d
	@docker exec php-demo php artisan key:generate
	@docker exec php-demo composer install
	@docker exec php-demo php artisan migrate --step
	@docker exec php-demo php artisan search:create

seed: ## Seed database
	@docker exec php-demo php artisan db:seed
