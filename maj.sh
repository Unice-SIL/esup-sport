#!/bin/bash

### Execution des commandes de mise à jour des dépendances
# echo composer install
# composer install

# echo yarn install
# yarn install

echo php bin/console assets:install --symlink assets
php bin/console assets:install --symlink assets

echo php bin/console doctrine:schema:update --force
php bin/console doctrine:schema:update --force

echo php bin/console d:s:u --em="statistique" -f
php bin/console d:s:u --em="statistique" -f

echo bazinga assets
php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js

echo asset .json
php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json

echo yarn encore
yarn encore prod --ucaEnv=prod

echo cache clear
php bin/console cache:clear --env=prod

#position sur le repertoire parent pour changer les fichier root en apache
cd ..
chown apache:apache Uca/ -R
