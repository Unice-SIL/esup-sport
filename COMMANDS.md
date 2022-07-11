* Commandes utilisées pour la création du projet.
> php symfony new Uca 3.4
> composer require paragonie/random_compat ^2
> composer require friendsofsymfony/user-bundle ~2.0
> composer require onurb/doctrine-yuml-bundle
> composer require stof/doctrine-extensions-bundle
> composer require symfony/webpack-encore-bundle
> php bin/console generate:bundle
<!-- Modification du composer.json : "psr-4": { "": "src/" }, -->
<!-- Modification de la config.yml -->
<!-- Suppression des scripts : buildBootstrap, installAssets que composer execute en fin d'update  -->
> composer update
> yarn add @symfony/webpack-encore --dev
> yarn add jquery --dev

* Commandes utiles
> php bin/console cache:clear
> php bin/console doctrine:generate:entities UcaBundle --no-backup
> php bin/console doctrine:generate:entities UcaBundle:ClasseActivite
> php bin/console doctrine:generate:form UcaBundle:ClasseActivite
> php bin/console doctrine:schema:update --dump-sql
> php bin/console doctrine:schema:update --force
> php bin/console translation:update --force --no-backup fr UcaBundle
> php bin/console uca:datatables:fixLang
> bin\batch database:dump
> bin\batch database:load
> bin\batch image:export
> bin\batch image:import
> php bin/console uca:table:annotation:load
> php bin/console yuml:mappings
> update_js.bat
> yarn encore dev
> yarn encore dev --watch
> yarn encore production
> yarn install

* Commande pour base de données statistique
> php bin/console doctrine:database:create --connection=statistique
> php bin/console doctrine:schema:update --force --em=statistique

* Utilitaires de debugage Symfony
> php bin/console debug:container > ~debug_sevices.txt
> php bin/console debug:event-dispatcher > ~debug_envent-dispacher.txt
> php bin/console debug:router > ~debug_routes.txt
> php bin/console doctrine:schema:validate > ~debug_doctrine-analysis.txt
> composer list > ~debug_composer-command.txt

* Urls
> https://symfony.com/doc/3.4/setup/file_permissions.html
> http://suapsweb.unice.fr
> http://univ-cotedazur.fr/
> http://localhost/Uca/web/app_dev.php
> https://services.renater.fr/federation/docs/shibboleth#la_brique_fournisseur_d_identites_idp
> http://activelamp.com/blog/development/shibboleth-authentication-in-symfony/

> http://www1.paybox.com/espace-integrateur-documentation/la-solution-paybox-system/appel-page-paiement/
> https://preprod-admin.paybox.com  ===== 199988843 / 1999888I


## Commandes utiles pour la migration

### Base de données de l'app

> php bin/console doctrine:migrations:diff --configuration=config/packages/migrations/app.yaml
> php bin/console doctrine:migrations:migrate --configuration=config/packages/migrations/app.yaml
> php bin/console doctrine:migrations:generate --configuration=config/packages/migrations/app.yaml

### Base de données de statistique

> php bin/console doctrine:migrations:diff --configuration=config/packages/migrations/stat.yaml
> php bin/console doctrine:migrations:migrate --configuration=config/packages/migrations/stat.yaml
> php bin/console doctrine:migrations:generate --configuration=config/packages/migrations/stat.yaml