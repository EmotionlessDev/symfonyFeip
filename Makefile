dc_build:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml build

dc_start:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml start

dc_stop:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml stop

dc_up:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml up -d --remove-orphans

dc_ps:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml ps

dc_logs:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml logs -f

dc_down:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml down -v --rmi=all --remove-orphans

dc_clear:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml down -v --remove-orphans
	docker volume prune -f
	docker network prune -f

app_bash:
	docker-compose --env-file ./.env.local -f .docker/docker-compose.yml exec -u www-data php-fpm bash
