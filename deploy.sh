#!/usr/bin/env bash
cd /root/avalara-tax-connector
echo "Pulling Changes from Bitbucket"
git pull

echo "Installing PHP Dependency (composer install)"
docker exec -t avalara-tax-connector_php_1 composer install

echo "Installing Node Project Dependency (npm install)"
docker exec -t avalara-tax-connector_php_1 npm install

echo "Dumping Project Dependency (composer dump-autoload)"
docker exec -t avalara-tax-connector_php_1 composer dump-autoload

echo "Migrating Database Changes (php artisan migrate)"
docker exec -t avalara-tax-connector_php_1 php artisan migrate --force

echo "Compiling Frontend Assets (npm run prod)"
docker exec -t avalara-tax-connector_php_1 npm run dev

echo "Restarting Docker Containers"
docker-compose restart
