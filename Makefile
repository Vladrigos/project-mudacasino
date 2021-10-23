up: docker-up
init: docker-down-clear docker-pull docker-build docker-up casino-init
test: casino-test
casino-init: casino-composer-install casino-wait-db casino-migrations

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

casino-test:
	docker-compose run --rm casino-php-cli php vendor/bin/phpunit

casino-composer-install:
	docker-compose run --rm casino-php-cli composer install

casino-wait-db:
	until docker-compose exec -T casino-postgres pg_isready --timeout 0 --dbname=app ; do sleep 1 ; done

casino-migrations:
	docker-compose run --rm casino-php-cli php bin/console doctrine:migrations:migrate --no-interaction

casino-fixtures:
	docker-compose run --rm casino-php-cli php bin/console doctrine:fixtures:load --no-interaction

cli:
	docker-compose run --rm casino-php-cli php bin/app.php

build-production:
	docker build --pull --file=casino/docker/production/nginx.docker --tag ${REGISTRY_ADDRESS}/casino-nginx:${IMAGE_TAG} casino
	docker build --pull --file=casino/docker/production/php-fpm.docker --tag ${REGISTRY_ADDRESS}/casino-php-fpm:${IMAGE_TAG} casino
	docker build --pull --file=casino/docker/production/php-cli.docker --tag ${REGISTRY_ADDRESS}/casino-php-cli:${IMAGE_TAG} casino

push-production:
	docker push ${REGISTRY_ADDRESS}/casino-nginx:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/casino-php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/casino-php-cli:${IMAGE_TAG}

deploy-production:
	ssh ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'rm -rf docker-compose.yml .env'
	scp -P ${PRODUCTION_PORT} docker-compose-production.yml ${PRODUCTION_HOST}:docker-compose.yml
	ssh ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "REGISTRY_ADDRESS=${REGISTRY_ADDRESS}" >> .env'
	ssh ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "IMAGE_TAG=${IMAGE_TAG}" >> .env'
	ssh ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose pull'
	ssh ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose --build -d'