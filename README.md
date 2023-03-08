Suaps
=====

# 1. Installation
composer
nodejs
yarn
imagick
https://www.php.net/manual/en/imagick.requirements.php
https://mlocati.github.io/articles/php-windows-imagick.html

# 2. Commandes pour install du projet / ou maj complète du projet 
#    /!\ environnement de développement seulement /!\ : 
D'abord, il faut supprimer et recréer la base de données uca (via phpmyadmin)
> composer update
> yarn install
> php bin/console uca:datatables:fixLang
> php bin/console doctrine:schema:update --force
> php bin/console uca:sql:load InitData.sql
> php bin/console uca:table:annotation:load
> php bin/console uca:logos:init
> update_js.bat

# 3. Configuration :
> Editer le php.ini d'Apache et modifier la variable max_input_vars à 1000000 :
  max_input_vars = 1000000
  date.timezone = "Europe/Paris"

# 4. Comptes de test SSO - Shibboleth
> Edtudiant : user = tsuaps   / mdp = Tx8UjhUe
> Personnel : user = tt707743 / mdp = ruH4Yesp


## Environnement de test (phpunit)

### Configuration
- Dans le __php.ini__ de votre php console (pas celui d'apache), ajouter la configuration suivante (zend_extension à adapter si besoin + vérifier avoir le dll php_xdebug) :
```
; XDEBUG Extension
[xdebug]
zend_extension="c:/wamp64/bin/php/php7.4.0/zend_ext/php_xdebug-3.1.1-8.1-vs16-x86_64.dll"
;xdebug.mode allowed are : off develop coverage debug gcstats profile trace
xdebug.mode =coverage
xdebug.output_dir ="c:/wamp64/tmp"
xdebug.show_local_vars=0
xdebug.log="c:/wamp64/logs/xdebug.log"
;xdebug.log_level : 0 Criticals, 1 Connection, 3 Warnings, 5 Communication, 7 Information, 10 Debug	Breakpoint
xdebug.log_level=7
```
- Mettre à jour la variable __DATABASE_URL__ dans __.env.test__
- > php bin/console doctrine:database:create --env=test
- > php bin/console doctrine:migrations:migrate --env=test (ou php bin/console doctrine:schema:update --env=test)
- > php bin/console doctrine:fixtures:load --env=test

- Remplir les tables emails, logo_parametrable et parametrage au démarrage du projet avec BddUpdates/2023_Migration_Evol.sql

### Création de test
> php bin/console make:test

### Lancer les tests
- Lancer tous les tests + génération report (report généré dans tests/coverage)
    > composer test

- Lancer un fichier de test spécifique
    > php bin/phpunit tests/path/to/file.php
