# MajoraOTAStore

[![Build Status](https://travis-ci.org/LinkValue/MajoraOTAStore.svg?branch=master)](https://travis-ci.org/LinkValue/MajoraOTAStore)

Host your iOS/Android apps for an easy installation on allowed user devices.

## Features

- **Admin/user interface** to manage your apps, builds and users
- Available languages: **English**, **French**
- **Token protected applications** (your build files aren't exposed to the internet and download links expire after a few minutes)
- [**X-Sendfile**](https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile) support to download builds more efficiently
- **API** (still in beta) to access data from somewhere else than the admin interface

## Glossary

- Application (or app): app general info, bound to several builds
- Build: application version which can be downloaded and installed on a device
- User: account identified by login and password, needed to be able to see applications and builds
- Downloader: user who can only download builds of application he has access to
- Admin: user who can create new applications/builds and manage the ones he has access to
- SuperAdmin: user who can do everything (including adding SuperAdmins)

## Installation

MajoraOTAStore is a "simple" [Symfony 3.3](http://symfony.com/doc/3.3/index.html) application.

### Server requirements

- [Linux](https://getgnulinux.org) (it may works on Windows/macOS but you can't blame us if it doesn't)
- [`php 5.6.19+`](http://php.net) (`php 7+` is recommended)
- [`MySQL`](https://www.mysql.com) (it should also work using [`MariaDB`](https://mariadb.org), if not => please let us know)
- [`Node.js`](https://nodejs.org) (along with `npm`)
- HTTP server supporting PHP (such as [`nginx`](http://nginx.org) + [`php-fpm`](http://php.net/manual/fr/install.fpm.php), etc.)

### Application setup

From your server (preferably **not** as `root`):
```shell
# copy sources from GitHub
cd /var/www
git clone https://github.com/LinkValue/MajoraOTAStore.git
cd MajoraOTAStore

# configure application
# by copying "app/config/parameters.yml.dist" to "app/config/parameters.yml"
# and editing "app/config/parameters.yml" to fit your context

# install dependencies and assets
# a Makefile (see https://www.gnu.org/software/make) is present to build the project from sources,
so just run the following command:
make prod-deploy
```

Notice that you'll probably also need to setup correct file permissions on the `var` directory, see [this guide](http://symfony.com/doc/3.3/setup/file_permissions.html) to know what you can do.

### HTTP server configuration

The HTTP server configuration looks like [any other Symfony application](https://symfony.com/doc/3.3/setup/web_server_configuration.html), just keep in mind the following points:

- `https` is required to download iOS applications (if you don't want to pay for a SSL certificate, we suggest to use [Certbot](https://certbot.eff.org) which is a [Let's Encrypt](https://letsencrypt.org) wrapper to generate official SSL certificates)
- you may want to increase the max upload size (either in PHP and HTTP server configuration) to be able to upload large build files
- [X-Sendfile](https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile) feature is supported to download protected build files, therefore it's recommended to configure your HTTP server to use it, if you don't, PHP will serve these files instead (which will increase memory usage)

More documentation for specific HTTP servers:

- [nginx + php-fpm](doc/configuration/nginx.md)

## API documentation

Before using the API, you must read [this documentation about how to be authenticated on the API side](doc/api/authentication.md).

- [/api/application](doc/api/application.md)
- [/api/build](doc/api/build.md)

## Contributing

[See our CONTRIBUTING guide](CONTRIBUTING.md)
