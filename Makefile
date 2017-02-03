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
	test -d web/bundles && rm -rf web/bundles/* || true
	test -d web/css && rm -rf web/css/* || true
	test -d web/js && rm -rf web/js/* || true
	bin/composer dump-autoload
	php bin/console cache:warmup
	php bin/console assets:install --symlink
	php bin/console assetic:dump --force

clean-assets:
	test -d web/css && rm -rf web/css/* || true
	test -d web/js && rm -rf web/js/* || true
	php bin/console assetic:dump --force

# Installation
install-bin:
	mkdir -p bin
	test -f bin/composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
	bin/composer self-update
	test -f bin/php-cs-fixer || wget http://get.sensiolabs.org/php-cs-fixer.phar -O bin/php-cs-fixer
	php bin/php-cs-fixer self-update
	test -f /usr/bin/ruby || (test -f /usr/local/bin/ruby && sudo ln -fs /usr/local/bin/ruby /usr/bin/ruby)

install-git-hooks:
	test -f .git/hooks/pre-commit || wget https://raw.githubusercontent.com/LinkValue/symfony-git-hooks/master/pre-commit -O .git/hooks/pre-commit
	chmod +x .git/hooks/pre-commit || true

install-composer:
	bin/composer install

install-bower:
	test -L bin/bower || (npm install bower && cd bin && ln -fs ../node_modules/bower/bin/bower)
	bin/bower install --config.interactive=false
	cd web && ln -fs ../vendor/bower_components/components-font-awesome/fonts
	cd web && ln -fs ../vendor/bower_components/flag-icon-css/flags

install: install-bin install-git-hooks install-composer install-bower db-build clean

# Update
update: update-composer update-bower clean

update-composer:
	bin/composer update

update-bower:
	bower update --config.interactive=false

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
test-prepare: test-install-bin install-composer install-bower db-build clean

test-install-bin:
	test -d bin/ || mkdir bin/
	test -f bin/composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
	bin/composer self-update

# Production
prod-install: install-bin
	bin/composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
	test -L bin/bower || (npm install bower && cd bin && ln -fs ../node_modules/bower/bin/bower)
	bin/bower install --config.interactive=false

prod-build:
	php bin/console doctrine:database:create --if-not-exists --env=prod
	php bin/console doctrine:migration:migrate -n --env=prod

prod-clean:
	rm -rf var/cache/*
	rm -rf var/logs/*
	rm -rf vendor/composer/autoload*
	rm var/bootstrap.php.cache
	test -d web/bundles && rm -rf web/bundles/* || true
	test -d web/css && rm -rf web/css/* || true
	test -d web/js && rm -rf web/js/* || true
	test -d web/fonts && rm -rf web/fonts/* || true
	bin/composer dump-autoload -o
	php bin/console cache:warmup --env=prod
	cp -rf vendor/bower_components/components-font-awesome/fonts web/
	php bin/console assets:install --env=prod
	php bin/console assetic:dump --force --env=prod

prod-deploy: prod-install prod-build prod-clean
