# Vm
vm-build:
	test -f ansible/group_vars/dev.local.yml || cp ansible/group_vars/dev.local.yml.dist ansible/group_vars/dev.local.yml
	vagrant up --provision || true

vm-install:
	vagrant provision || true

vm-rebuild:
	vagrant destroy -f || true
	vagrant up --provision || true

# First install
prepare: install db-build
	@echo "Project AppBuildServer is built !"

# Clean
clean:
	rm -rf app/cache/*
	rm -rf app/logs/*
	rm -rf vendor/composer/autoload*
	rm app/bootstrap.php.cache
	test -d web/bundles && rm web/bundles/* || true
	test -d web/css && rm web/css/* || true
	test -d web/js && rm web/js/* || true
	./bin/composer dump-autoload
	./bin/composer run-script setup-bootstrap -vv
	php app/console cache:warmup
	php app/console cache:warmup --env=prod
	php app/console assets:install --symlink
	php app/console assetic:dump --force

clean-assets:
	test -d web/css && rm web/css/* || true
	test -d web/js && rm web/js/* || true
	php app/console assetic:dump --force

# Installation
install-bin:
	test -d bin/ || mkdir bin/
	test -f bin/composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
	./bin/composer self-update
	test -f bin/php-cs-fixer || wget http://get.sensiolabs.org/php-cs-fixer.phar -O bin/php-cs-fixer
	php bin/php-cs-fixer self-update

install-git-hooks:
	test -f .git/hooks/pre-commit || wget https://raw.githubusercontent.com/LinkValue/symfony-git-hooks/master/pre-commit -O .git/hooks/pre-commit
	chmod +x .git/hooks/pre-commit || true

install-composer:
	./bin/composer install

install-bundle:
	bundle install --path vendor/gems

install-bower:
	bower install --config.interactive=false
	cd web && ln -fs ../vendor/bower_components/components-font-awesome/fonts
	cd web && ln -fs ../vendor/bower_components/flag-icon-css/flags

install: install-bin install-git-hooks install-composer install-bundle install-bower clean

# Update
update: update-composer update-bower clean

update-composer:
	./bin/composer update

update-bower:
	bower update --config.interactive=false

# Database
db-build:
	php app/console doctrine:database:create --if-not-exists
	php app/console doctrine:migrations:migrate -n
	php app/console doctrine:fixtures:load -n || true

db-trash:
	php app/console doctrine:database:drop --force --if-exists
	php app/console doctrine:database:create

db-rebuild: db-trash db-build

db-force: db-trash
	php app/console doctrine:schema:update --force
	php app/console doctrine:fixtures:load -n || true

db-update:
	php app/console doctrine:schema:validate || test "$$?" -gt 1
	php app/console doctrine:migrations:migrate -n
	php app/console doctrine:migrations:diff
	php app/console doctrine:migrations:migrate -n

# Tests
test-prepare: test-install-bin install-composer install-bower db-build clean

test-install-bin:
	test -d bin/ || mkdir bin/
	test -f bin/composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
	./bin/composer self-update

run-tests:
	rm -rf web/tests-coverage/*
	./bin/phpunit -c app --testsuite appbuild_project --coverage-html web/tests-coverage
	@echo "\nCoverage report : \n\033[1;32m http://app-build.dev/tests-coverage/index.html\033[0m\n"

# Production
prod-install: install-bin
	./bin/composer install --prefer-dist --no-dev
	test -L ./bin/bower || npm install bower
	test -L ./bin/bower || (cd bin && ln -fs ../node_modules/bower/bin/bower)
	./bin/bower install

prod-build:
	php app/console doctrine:migration:migrate -n --env=prod

prod-clean:
	rm -rf app/cache/*
	rm -rf app/logs/*
	rm -rf vendor/composer/autoload*
	rm app/bootstrap.php.cache
	test -d web/bundles && rm -rf web/bundles/* || true
	test -d web/css && rm -rf web/css/* || true
	test -d web/js && rm -rf web/js/* || true
	test -d web/fonts && rm -rf web/fonts/* || true
	test -d web/flags && rm -rf web/flags/* || true
	./bin/composer dump-autoload -o
	./bin/composer run-script setup-bootstrap -vv
	php app/console cache:warmup --env=prod
	cp -rf vendor/bower_components/components-font-awesome/fonts web/
	cp -rf vendor/bower_components/flag-icon-css/flags web/
	php app/console assets:install --env=prod
	php app/console assetic:dump --force --env=prod

prod-deploy: prod-install prod-build prod-clean
