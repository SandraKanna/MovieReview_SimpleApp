PROJECT := php-movie-reviews
SERVICE := web
COMPOSE := docker compose

.DEFAULT_GOAL := help

help: ## Show available targets
	@echo "$(PROJECT) targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## ' Makefile | awk 'BEGIN {FS=":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

start: ## build the image (if it doesnt exist) and launch the server
	$(COMPOSE) up -d --build
	@echo "Open: http://localhost:8080/"

stop: #stop the server
	$(COMPOSE) down

restart: ## restart the server
	$(COMPOSE) restart $(SERVICE)

logs: ## show logs
	$(COMPOSE) logs -f $(SERVICE)

ps: ## list active containers
	$(COMPOSE) ps -a

clean: ## Down + remove local images/volumes of this project
	$(COMPOSE) down -v --rmi local --remove-orphans

destroy: clean ## Remove EVERYTHING (careful)
	docker system prune -af
	@rm -rf src/uploads
	@echo "Everything removed, including volumes and cached images."

.PHONY: start stop restart logs ps clean destroy
