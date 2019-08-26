Charte de développement
=======================


Symfony
-------

### Principe de base : 
* placer un maximum de code dans les entités.

### Entities : 
* On place toutes les propriétés dans une #region Propriétés
* On place toutes le méthodes / getters / setters spécifiques dans une #region Méthodes
* Les autres méthodes / getters / setters vont êtres générés automatiquement à la fin de l'entité
* la bonne pratique est, après chaque modification de l'entité de supprimé les méthodes générées automatiquement puis de les recréer avec la commande 
      > php bin/console doctrine:generate:entities UcaBundle --no-backup
* Si on a besoin uniquement d'un id pour faire une affectation, il faut utiliser le getReference et non pas le find : 
      > $statut = $this->em->getRepository(StatutUtilisateur::class)->find(1); **KO**
      > $statut = $this->em->getReference(StatutUtilisateur::class, 1); **OK**
      > $event->getUser()->setStatut($statut);
  Cela est important, ça permet d'économiser une requête SQL.

### Doctrine :
* Bien verifier que tout est ok au niveau de Doctrine
* On peut vérifier dans le profiler de symfony ou via la commande suivante permet d'extraires toutes les erreurs Doctrine
      > php bin/console doctrine:schema:validate > doctrine-analysis.txt~
* Utiliser correctement les arrayCollection et les criteria de doctrine

### Repository:
4 Types de fonctions (en fonction de ce qu'on retourne) : [mettre chaque type dans une balise region et dans l'ordre suivant]
* criteria : nom de fonction préfixer par criteria / fonction statique
* queryBuilder : nom de fonction préfixer par qb 
* query : nom de fonction préfixer par query 
* resulat : pas de préfixe
==> Pour l'instant je suis pas sûr de ça.

### A faire
* A voir si il n'est pas possible de créer une fonction qui permet de remplacer les getters / setters en utilisant
>  call_user_method ( string $method_name , object &$obj [, mixed $... ] ) : mixed
On pourrait se baser sur des annotations pour voir si on doit permettre l'utilisation du setter / du getter sur la propriété
* A voir si on peut générer le sitemap.yml uniquement grace à des annotations associées à des routes. @Sitemap(parent=UcaWeb_Accueil, menu=false...)

### Traduction:
 /!\ Ne jamais utiliser de code de traduction avec concatenation de variable : 
      ==> ('libelle.' ~ codeAction) | trans ==> KO !
      ==> 'libelle' | trans({'%action%': codeAction}) ==> OK -- libelle: libelle %action% (on peut aussi utiliser transChoice)
L'idée est de pouvoir regénérer facilement l'exaustivité des traductions grace à la commande : 
 > php bin/console translation:update --force --no-backup fr UcaBundle
 ==> ce sera surement plus facile sur Symfony 4 car j'ai l'impression que pour le moment, on peut pas gérer tous les cas sans concatener avec des variables.

### Les tables techniques :
 - Comment gérer les listes de valeurs en conservant la possibilité de traduire ?
 - Une entité globale Valeur avec des entités qui héritent pour chaque liste de valeur
 - Une enuméraion (A étudier dans doctrine)
 - Un code integer / un code texte ?

### Formatage des dates:
Pour que le nom des mois et des jours soient traduits
http://userguide.icu-project.org/formatparse/datetime
Il faut créer un filtre twig (cf projet unice)

### Architecture des dossier
symfony-project/
├─ ...
└─ src/
   └─ Model
      └─ Annotation
      └─ Entity
      └─ ReferenceEntity (id, libelle, ordre, modifiable)
      └─ EventListener (un fichier par entity)
      └─ NonePersistedEntity ?? (exemple Contact.php)
      └─ Repository
   └─ Core ? / 
      └─ Command
      └─ Controller
      └─ Datatable
      └─ Form
      └─ Service
   └─ Resources
      └─ config
      └─ sql
      └─ translations
      └─ views


Projet
------

### Développement : 
* Avant de commiter, regarder bien ce que vous allez commiter. 
  Refechissez si on ne peut pas mieux faire.
  Si on a fait un copier coller d'un code sur internet, il faut bien renomer les noms de variables et des fonctions pour qu'elles aient un sens pour nous.
* Bien choisir le nom des variables, classes, fonctions, noms de rôles... Ce temps de reflexion est très important et fait parti du travail d'un dévellopeur.
  Pour bien choisir les noms, n'hésitez pas à demander l'avis de vos collègues. Quand on est trop la tête dans le guidon on choisit souvent mal car on n'a pas assez de recul. 
  On peut aussi regarder ce qui est fait ailleurs pour s'en inspirer.
  une application bien construite et bien organisée est beaucoup, beaucoup plus simple à maintenir et ne nécessite quasiment aucun commentaire.
* Supprimer le code inutile. Quand on déplace du code, qu'on factorise... il faut supprimer le code qui ne sert plus.
  Quand on copie du code d'internet, il faut personaliser le code avec nos normes, les bons noms de variables et supprimer les parties du code qui ne nous servent pas.

### Git : 
* Fichier a ne pas commiter : commence par ~
* Essayer de mettre en place git flow

### Formattage :
* Utiliser ALT + SHIFT + F pour formater les fichiers correctement. 
--> pour que ça fonctionne, il faut utiliser les extensions VS Code suivantes :
      - PHP Intelephense pour PHP
      - Twig Language 2 pour le twig
      - Yaml 0.4.1 pour le yml
      - SCSS Formatter pour le scss
* Cela fonctionne très bien et cela permet d'avoir tous les même règles d'indentation et évite qu'on commit de la mise en page.

### Données de Test : 
* L'idéal serait d'avoir un fichier avec un dump d'une BDD avec des données de test.
* ce fichier SQL sera commité sur git. (Dump.sql-) Le "-" permet de faire en sorte que VS Code n'analyse pas le fichier.
* Quand un développeur veut modifier quelque chose dans le fichier Dump.sql-, il charge d'abord le fichier pour écraser toutes ses modifications de test puis il refait un dump. Puis il commit ce fichier.
* Dans l'idéal, il faudrait que le dump contienne un fichier par table cela permettrait de mieux gérer les conflits de commit.
* Voir projet Unice : dump / load + sauvegarde et chargement des images.


Languages de base
-----------------

### PHP :
Déclaration des variables php (surtout pour les entités)
      /** @var \UcaBundle\Entity\Commande $commande */
      $commande;
Cela n'est pas indispensable mais ça permet de mieux faire fonctionner l'auto-complétion dans VSCode.

### Javascript: 
* utiliser une variable gloable pour stocker toutes les fonctions :
      _uca = {}
      _uca.getDate = function () { return new Date(); }

### css / js : 
* Toutes les class css qui sont utilisées par les développeurs pour faire du js doivent être préfixé de js-
exemple : js-texte-inscrit-clone.
* Ces classes ne doivent pas servir à faire du css
* Romain, tu ne dois jamais touché a ces classes css la (surtout pas de renomage)
PS : je mets en place cela car il y a eu un loupé sur une classe css qui a été renomé et ça a fait bugué une fonctionnalité.
Il faut donc absoluement qu'on arrive a savoir facilement si une classe est pour le css ou pour le js.
* A voir avec romain si on ne peut pas créer un service / extension twig qui permettrait d'utiliser des groupes de classe dans le css.

### Bootstrap 4 :
A voir avec romain...
https://hackerthemes.com/bootstrap-cheatsheet/#carousel-caption


