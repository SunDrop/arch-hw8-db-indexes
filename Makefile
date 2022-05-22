up:
	docker-compose up -d

down:
	docker-compose down

stop:
	docker-compose stop

logs:
	docker-compose logs -f

ps:
	docker-compose ps

rebuild:
	@echo "\033[32mRebuild containers...\033[0m"
	docker-compose build --force-rm --no-cache

php:
	@echo "\033[32mEntering into php container...\033[0m"
	docker-compose exec php bash

db:
	@echo "\033[32mEntering into DB container...\033[0m"
	docker-compose exec db bash
