# MajoraOTAStore

[![Build Status](https://travis-ci.org/LinkValue/MajoraOTAStore.svg?branch=master)](https://travis-ci.org/LinkValue/MajoraOTAStore)

Permet l'hébergement de builds d'application et leur téléchargement pour une installation simple sur les devices qui sont autorisés à la télécharger.

## Installation du projet en dev
Avant de commencer, installer Vagrant : http://docs.vagrantup.com/v2/getting-started/index.html.

### 1. Git
```bash
git clone git@github.com:LinkValue/MajoraOTAStore.git
cd MajoraOTAStore
```

### 2. Hosts
Pour travailler avec la machine virtuelle, ajouter cet host à la machine locale :
```
### MajoraOTAStore ###
192.168.100.70 app-build.dev
```

### 3. Machine virtuelle
```bash
make vm-build
```
La commande va créer et provisionner la machine.
Pour custom certaines variables du provisionning, le fichier ```ansible/group_vars/dev.local.yml``` n'est pas versionné et inclu dans le script.

### 4. Bootstrap du projet
Sur le projet, toutes les commandes utilisant Php doivent être lancées depuis la machine virtuelle, pour éviter les conflits de versions et de permissions.
```ssh
vagrant ssh
cd /var/www/MajoraOTAStore
make prepare
```

## Utilisation courante
La commande ```make install``` est à utiliser pour installer les bonnes versions des dépendances, des binaires etc... Elle n'écrase aucune données.

La commande ```make db-build``` lance la construction de la base de données et le chargement des fixtures.
Pour recréer la base : ```make db-rebuild```.
Pour mettre à jour le modèle depuis les schémas Doctrine : ```make db-rebuild db-update db-build```.

Pour lancer les tests, lancer la commande ```make run-tests```.
La couverture de tests est par défaut disponible ici en environnement de développement : http://app-build.dev/tests-coverage/index.html
