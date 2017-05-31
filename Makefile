.PHONY: provision start stop ssh destroy rebuild clean install update

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
	rm -rf var/cache/*
	rm -rf var/logs/*
	rm -rf vendor/composer/autoload*
	rm var/bootstrap.php.cache
	bin/composer dump-autoload
	php bin/console cache:warmup
	npm run build

# Installation
install-bin:
	mkdir -p bin
	test -f bin/composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
	bin/composer self-update
	test -f bin/php-cs-fixer || wget http://get.sensiolabs.org/php-cs-fixer.phar -O bin/php-cs-fixer
	php bin/php-cs-fixer self-update

install-git-hooks:
	test -f .git/hooks/pre-commit || wget https://raw.githubusercontent.com/LinkValue/symfony-git-hooks/master/pre-commit -O .git/hooks/pre-commit
	chmod +x .git/hooks/pre-commit || true

install-composer:
	bin/composer install

install-npm:
	npm install

install-jwt:
	mkdir -p var/jwt
	test -f var/jwt/private.pem || openssl genrsa -out var/jwt/private.pem -passout pass:Majora -aes256 4096
	test -f var/jwt/public.pem || openssl rsa -in var/jwt/private.pem -passin pass:Majora -pubout -out var/jwt/public.pem

install: install-bin install-git-hooks install-composer install-jwt install-npm db-build clean

# Update
update: update-composer clean

update-composer:
	bin/composer update

update-parameters:
	bin/composer run-script set-parameters-yml -vv

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

# Tests
test-prepare: test-install-bin install-composer install-npm db-build clean

test-install-bin:
	test -d bin/ || mkdir bin/
	test -f bin/composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
	bin/composer self-update

# Production
prod-install: install-bin
	SYMFONY_ENV=prod bin/composer install --no-dev --optimize-autoloader --no-interaction
	npm install

prod-build:
	php bin/console doctrine:database:create --if-not-exists --env=prod
	php bin/console doctrine:migration:migrate -n --env=prod

prod-clean:
	rm -rf var/cache/*
	rm -rf var/logs/*
	rm -rf vendor/composer/autoload*
	rm var/bootstrap.php.cache
	bin/composer dump-autoload -o
	php bin/console cache:warmup --env=prod
	npm run build

prod-deploy: prod-install prod-build prod-clean
