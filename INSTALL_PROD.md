UCA SPORT - INSTALLATION PROD
=============================

Nouvelle installation
---------------------

### Sauvegarde

Si l'application est déjà installée
Sauvegarder la base de données
> mysqldump -h mysql2.prive.unice.fr -u uca_sport -p uca_sport > /home/lepin/Uca/uca_sport.sql
Sauvegarder le parameters.yml
> cp /web/sport.univ-cotedazur.fr/html/Uca/app/config/symfony/parameters.yml /home/lepin/Uca/parameters.yml

### GIT :

* créer le repertoire /web/sport.univ-cotedazur.fr/html/Uca puis cloner le dépot
> cd /web/sport.univ-cotedazur.fr/html/Uca
> git clone https://github.com/Acatus-dev/Uca.git
>> username: DSI-SYSTEME
>> password: sYSTEME1

### Configuration de l'application :

* modifier le fichier app/config/symfony/parameters.yml.dist
> vi app/config/symfony/parameters.yml.dist
parameters:
    database_host: mysql2.prive.unice.fr
    database_port: null
    database_name: uca_sport
    database_user: uca_sport
    database_password: ****************
    mailer_transport: smtp
    mailer_host: smtp.unice.fr
    mailer_port: 25
    mailer_encryption: null
    mailer_user: null
    mailer_sender: uca-sport@unice.fr
    mailer_password: null
    mailer_auth_mode: null
    mailer_name: 'Uca Sport'
    secret: 98e78e48892642ddbe22ca2cdb74ac607130daa8

### Modification du owner du repertoire
> chown apache:apache /web/sport.univ-cotedazur.fr -R

### Execution des commandes de mise à jour des dépendances:
> composer update
> yarn install
> php bin/console assets:install --symlink assets
> php bin/console doctrine:schema:update --force
> php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js
> php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json
> yarn encore prod --ucaEnv=prod
> php bin/console cache:clear --env=prod


Livraison d'un correctif
------------------------

### GIT :
* Mettre à jour le dépot
> git pull

### Configuration 
* Remettre la bonne version du parameters.yml
> cp app/config/symfony/parameters.yml.dist app/config/symfony/parameters.yml
* vider le cache
> php bin/console cache:clear --env=prod

Livraison d'une mise à jour
---------------------------

### Sauvegarde

Sauvegarder la base de données
> mysqldump -h mysql2.prive.unice.fr -u uca_sport -p uca_sport > /home/lepin/Uca/uca_sport.sql

### GIT :

* Mettre à jour le dépot
> git pull

### Execution des commandes de mise à jour des dépendances
> composer update
> yarn install
> php bin/console assets:install --symlink assets
> php bin/console doctrine:schema:update --force
> php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js
> php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json
> yarn encore prod --ucaEnv=prod
> php bin/console cache:clear --env=prod

Activation du mode Debug
------------------------
> cp web/~app_dev.php.dev web/app_dev.php

Désactivation du mode Debug
------------------------
> cp web/~app_dev.php web/app_dev.php

Paybox : activation du mode test
--------------------------------
> cp app/config/bundles/paybox.test.yml app/config/bundles/paybox.yml

Paybox : activation du mode prod
--------------------------------
> cp app/config/bundles/paybox.prod.yml app/config/bundles/paybox.yml