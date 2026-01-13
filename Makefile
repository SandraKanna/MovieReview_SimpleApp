PROJECT := php-movie-reviews
SERVICE := web
COMPOSE := docker compose

.DEFAULT_GOAL := help

help: ## Show available targets
	@echo "$(PROJECT) targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## ' Makefile | awk 'BEGIN {FS=":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

start: ## build the image (if it doesnt exist) and launch the server
	$(COMPOSE) up -d --build
	@echo "✅ App running → http://localhost:8000/"

stop: #stop the running containers
	$(COMPOSE) stop

restart: ## restart the server
	$(COMPOSE) restart $(SERVICE)

logs: ## show logs
	$(COMPOSE) logs -f $(SERVICE)

ps: ## list active containers
	$(COMPOSE) ps -a

clean: ## Remove stopped containers but KEEP data volumes (uploads + db)
	$(COMPOSE) down --remove-orphans
	@echo "🧹 Containers removed."

destroy: clean ## Remove EVERYTHING (careful)
	docker system prune -af
	$(COMPOSE) down -v --rmi all --remove-orphans
	@docker system prune -af
	@rm -rf ./src/.uploads
	@echo "Everything removed, including volumes and cached images."

.PHONY: help start stop restart logs ps clean destroy

