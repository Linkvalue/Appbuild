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


### Common tasks
```bash
# rebuild the whole database with clean fixtures
make db-rebuild
# update your database schema after adding/removing stuff in your data model
make db-rebuild db-update db-build
# run integration tests
bin/behat
```
