# How to contribute

## Development

This project uses [LinkValue/majora-ansible-vagrant v2.2.0](https://github.com/LinkValue/majora-ansible-vagrant/tree/v2.2.0) as its development environment,
so please head to this link and fulfill the **Requirements** section for your OS before anything else.

### Installation

#### 1. Clone project
```bash
git clone git@github.com:LinkValue/MajoraOTAStore.git
cd MajoraOTAStore
```

#### 2. Virtual machine provisioning
```bash
make provision
```

Note: If it fails at some point, don't hesitate to re-run this command several times.

#### 3. Bootstrap project
```bash
# connect to the VM
make ssh
# then install the project for development
make install
```

#### 4. Enjoy

You should see your application up and running at http://majoraotastore.dev/app_dev.php/

Try to login using one of the following credentials:
```
# login => password
superadmin@superadmin.fr => superadmin
admin@admin.fr => admin
user@user.fr => user
```

### Frontend development

All the assets (CSS/JS) are handled by Webpack.

By default, you'll have to run `npm run build` each time you edit an asset file to see the modification in your browser (after refreshing it).

But you can also use the webpack-dev-server to watch assets modification without needed to rebuild assets or even refreshing your browser.

First, activate the webpack-dev-server support in your `parameters.yml` file:

```yml
# parameters.yml

...

use_webpack_dev_server: true

```

Then run the following command:

```bash
npm start
```

### Common tasks
```bash
# rebuild the whole database with clean fixtures
make db-rebuild
# update your database schema after adding/removing stuff in your data model
make db-rebuild db-update db-build
# run integration tests
bin/behat
```


### Get a token (WIP)
```bash
curl -X POST http://majoraotastore.dev/app_dev.php/api/login_check -d _username=admin@admin.fr -d _password=admin
```
output :
```
{"token":"eyJhbGciOiJSUzI1NiJ9.eyJyb2xlcyI6WyJS...2FcOf0m0juoxmorX_N9bNO0cucRJuLTf5-5PZCsohqAFMcXdPX50Qvn8"}
```