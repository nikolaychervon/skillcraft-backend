APP=skillcraft_back

up:
	docker-compose up -d

build:
	docker-compose up -d --build
	docker exec -it $(APP) composer install
	docker exec -it $(APP) sh -c 'if [ ! -f .env ]; then cp .env.example .env; fi'
	make migrate

migrate:
	docker exec -it $(APP) php artisan migrate

down:
	docker-compose down

bash:
	docker exec -it $(APP) bash