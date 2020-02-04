# How to contribute

## Development

Requirements
- Docker 
- Docker compose 

### Installation

 ```shell
# Add this line to the host file /etc/hosts
127.0.0.1 local.appbuild.com

# copy sources from GitHub
git clone https://github.com/LinkValue/Appbuild.git
cd Appbuild

# Start project
docker-compose up -d

# Setup api
# Get a bash shell in the nginx container
docker exec -it appbuild_nginx bash
cd /app

# Install JWT
mkdir -p var/jwt
test -f var/jwt/private.pem || openssl genrsa -out var/jwt/private.pem -passout pass:Appbuild -aes256 4096
test -f var/jwt/public.pem || openssl rsa -in var/jwt/private.pem -passin pass:Appbuild -pubout -out var/jwt/public.pem

# Setup db
# use php-fpm container
docker exec -it appbuild_php_fpm php bin/console doctrine:database:create --if-not-exists
docker exec -it appbuild_php_fpm php bin/console doctrine:migrations:migrate -n
docker exec -it appbuild_php_fpm php bin/console doctrine:fixtures:load -n --fixtures=src/UserBundle

# Reset db
# use php-fpm conatainer
docker exec -it appbuild_php_fpm php bin/console doctrine:database:drop --force --if-exists
docker exec -it appbuild_php_fpm  php bin/console doctrine:database:create

# You can also watch asset modifications
docker logs -f appbuild_node

# Start webpack server if is not started yet, use node container
docker exec -it appbuild_node npm start
```

You should see your application up and running at http://local.appbuild.com

Try to login using one of the following credentials:
```
# login => password
superadmin@superadmin.fr => superadmin
admin@admin.fr => admin
user@user.fr => user
```

### Common tasks
```shell
# rebuild the whole database with clean fixtures
make db-rebuild
# update your database schema after adding/removing stuff in your data model
make db-rebuild db-update db-build
# run functional tests
bin/behat
```
