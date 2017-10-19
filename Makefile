.PHONY: provision start stop ssh destroy rebuild clean install update clean

#
# Main targets
#
provision: vm-provision
start: vm-up
stop: vm-halt
ssh: vm-ssh
destroy: vm-destroy
rebuild: vm-rebuild

#
# VM
#
vm-ssh:
	vagrant ssh

vm-up:
	vagrant up

vm-halt:
	vagrant halt

vm-provision:
	vagrant up --no-provision
	vagrant provision

vm-destroy:
	vagrant destroy

vm-rebuild: vm-destroy vm-provision

# Clean
clean:
	php bin/console cache:clear --no-warmup
	php bin/console cache:warmup

# Installation
install-bin-prod:
	mkdir -p bin
	test -f bin/composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer

install-bin-dev:
	mkdir -p bin
	test -f bin/php-cs-fixer || wget http://get.sensiolabs.org/php-cs-fixer.phar -O bin/php-cs-fixer

install-composer:
	php bin/composer install

install-npm:
	npm install

install-jwt:
	mkdir -p var/jwt
	test -f var/jwt/private.pem || openssl genrsa -out var/jwt/private.pem -passout pass:Appbuild -aes256 4096
	test -f var/jwt/public.pem || openssl rsa -in var/jwt/private.pem -passin pass:Appbuild -pubout -out var/jwt/public.pem

install: install-bin-dev install-bin-prod install-composer install-jwt install-npm db-build assets-build

# Update
update: update-composer clean

update-composer:
	php bin/composer update

# Database
db-build:
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate -n
	php bin/console doctrine:fixtures:load -n --fixtures=src/UserBundle
	php bin/console hautelook_alice:doctrine:fixtures:load -n --append

db-trash:
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create

db-rebuild: db-trash db-build

db-update:
	php bin/console doctrine:schema:validate || test "$$?" -gt 1
	php bin/console doctrine:migrations:migrate -n
	php bin/console doctrine:migrations:diff
	php bin/console doctrine:migrations:migrate -n

# Assets
assets-build: clean npm-build

assets-watch: clean
	npm start

npm-build:
	npm run build

# Production
prod-install: install-bin-prod
	SYMFONY_ENV=prod php bin/composer install --no-dev --optimize-autoloader --no-interaction
	npm install

prod-build:
	php bin/console doctrine:database:create --env=prod --if-not-exists
	php bin/console doctrine:migration:migrate --env=prod -n
	npm run build

prod-clean:
	php bin/console cache:clear --env=prod --no-warmup
	php bin/console cache:warmup --env=prod

prod-deploy: prod-install prod-build prod-clean
