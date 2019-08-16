Suaps
=====

# 1. Installation
composer
nodejs
yarn
imagick
https://www.php.net/manual/en/imagick.requirements.php

# 2. Commandes pour install du projet / ou maj complète du projet 
#    /!\ environnement de développement seulement /!\ : 
D'abord, il faut supprimer et recréer la base de données uca (via phpmyadmin)
> composer update
> yarn install
> php bin/console uca:datatables:fixLang
> php bin/console doctrine:schema:update --force
> php bin/console uca:sql:load InitData.sql
> php bin/console uca:table:annotation:load
> update_js.bat

# 3. Configuration :
> Editer le php.ini d'Apache et modifier la variable max_input_vars à 1000000 :
  max_input_vars = 1000000

# 4. Comptes de test SSO - Shibboleth
> Edtudiant : user = tsuaps   / mdp = Tx8UjhUe
> Personnel : user = tt707743 / mdp = ruH4Yesp
