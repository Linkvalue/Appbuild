# How to contribute

## Development

This project uses [LinkValue/majora-ansible-vagrant v2.2.0](https://github.com/LinkValue/majora-ansible-vagrant/tree/v2.2.0) as its development environment,
so please head to this link and fulfill the **Requirements** section for your OS before anything else.

### Installation

#### 1. Clone project
```shell
git clone git@github.com:LinkValue/Appbuild.git
cd Appbuild
```

#### 2. Virtual machine provisioning
```shell
make provision
```

Note: If it unluckily fails at some point, don't hesitate to re-run this command several times.

#### 3. Install project
```shell
# connect to the VM
make ssh
# install the project for development (you'll have to press "Enter" several times to keep default parameters)
make install
# serve assets for development environment
npm start
```

#### 4. Enjoy

You should see your application up and running at http://appbuild.dev/app_dev.php/

Try to login using one of the following credentials:
```
# login => password
superadmin@superadmin.fr => superadmin
admin@admin.fr => admin
user@user.fr => user
```

### Frontend development

Front assets (css/js/images) are handled by [Webpack](https://webpack.js.org/).

When you're in development environment (i.e. `http://appbuild.dev/app_dev.php/...`), the project is configured to use webpack dev server to serve assets, it means that the project will seems to be broken until you run the following command:
```shell
npm start
```

When you're in production environment (i.e. `http://appbuild.dev/...`), the project will use the assets found in `web/assets`, it means that you'll have to run `npm run build` each time you edit an asset file to see the modification in your browser (after refreshing it). 

### Common tasks
```shell
# rebuild the whole database with clean fixtures
make db-rebuild
# update your database schema after adding/removing stuff in your data model
make db-rebuild db-update db-build
# run functional tests
bin/behat
```
