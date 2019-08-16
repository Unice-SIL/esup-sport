/* SET SQL_SAFE_UPDATES = 0; */

TRUNCATE TABLE `dhtmlx_date`;
DELETE FROM `ext_translations`;

DELETE FROM `format_activite_niveau_sportif`;
DELETE FROM `creneau`;
DELETE FROM `format_activite`;
DELETE FROM `activite` ;

DELETE FROM `utilisateur_groupe`;
DELETE FROM `groupe`;
DELETE FROM `utilisateur`;

DELETE FROM `montant_tarif_profil_utilisateur`;
DELETE FROM `type_autorisation`;
DELETE FROM `comportement_autorisation`;

DELETE FROM `ressource`;
DELETE FROM `etablissement`;
DELETE FROM `tarif`;
DELETE FROM `image_fond`;

DELETE FROM `classe_activite`;
DELETE FROM `niveau_sportif`;
DELETE FROM `profil_utilisateur`;
DELETE FROM `type_activite`;
DELETE FROM `texte`;


INSERT INTO `type_activite` (`id`, `libelle`) VALUES
(1, 'Sport Fr'),
(2, 'Culture Fr');

/* TARIF */
INSERT INTO `tarif` 
    (`id`, `libelle`, `modification_montants`) 
VALUES
    (15, 'CVEC', ''),
    (18, 'Badminton', ''),
    (25, 'Cartes Colorées', '');

/* ETABLISSEMENTS */
INSERT INTO `etablissement` 
    (`id`,`code`,`libelle`, `adresse`, `code_postal`, `ville`, `image`) 
VALUES 
    (1,'CSU VALROSE (Campus Sciences)','CSU VALROSE (Campus Sciences)', '5 rue de la rose', '06000', 'Nice', 'campus.jpg'),
    (2,'CSU CARLONE (Campus Carlone)','CSU CARLONE (Campus Carlone)','10 boulevard requin', '06000', 'Nice','campus.jpg'),
    (3,'CSU TROTABAS (Campus droit)','CSU TROTABAS (Campus droit)','63 avenue de la salade', '06000', 'Nice', 'campus.jpg'); 

/* RESSOURCES */
INSERT INTO `ressource` 
    (`id`,`etablissement_id`,`libelle`,`description`,`nomenclature_rus`,`superficie`,`source_referentiel`,`format`,`image`) 
VALUES 
    (19,1,'Mur d\'Escalade','Mur d\'Escalade',NULL,25,false,'Lieu','murEscalade.jpg'),
    (17,1,'Terrain de Beach Volley','Terrain de Beach Volley',NULL,300,false,'Lieu','beachVolley.jpg'),
    (18,NULL,'VTT','VTT',NULL,NULL,false,'Materiel','vtt.jpg'),
    (20,NULL,'Skis','Skis',NULL,NULL,false,'Materiel','skis.jpg');

/* CLASSE D'ACTIVITE */
INSERT INTO `classe_activite`
    (`id`, `type_activite_id`, `libelle`, `image`, `updated_at`) 
VALUES 
    (1, 1, 'Sport en salle','salle.jpg','2019-01-01 00:00:00'),
    (2, 1, 'Sport de Plein Air', 'pleinAir.jpg','2019-01-01 00:00:00'),
    (3, 1, 'Sport de raquette', 'Raquette.jpg','2019-01-01 00:00:00'),
    (4, 1, 'Sport de baballe','balle.jpg','2019-01-01 00:00:00'),
    (5, 1, 'Sport d\'eau', 'eau.jpg','2019-01-01 00:00:00'),
    (6, 1, 'E-sport' ,'geek.jpg','2019-01-01 00:00:00'),
    (7, 1, 'Divers','divers.jpg','2019-01-01 00:00:00');


/* COMPORTEMENT */ 
INSERT INTO `comportement_autorisation` 
    (`id`, `libelle`, `code_comportement`) 
VALUES 
    (1, 'Cotisation','cotisation'),
    (2, 'Justificatif à fournir','justificatif'),
    (3, 'Case à cocher','case'),
    (4, 'Achat de Carte','carte');

/* TYPE D'AUTORISATION */
INSERT INTO `type_autorisation` 
    (`id`,`comportement_id`,`libelle`,`informations_complementaires`,`tarif_id`) 
VALUES 
    (2, 1, 'Cotisation sportive',NULL,15),
    (3, 3, 'Certificat médical','Je certifie être en possession d\'un certificat médical pour participer à cette activité',NULL),
    (4, 2, 'Autorisation plongée',NULL,NULL),
    (5, 2, 'Autorisation escalade',NULL,NULL),
    (13, 4, 'Carte Musculation','RAS',25),
    (14, 4, 'Carte Tennis',NULL,NULL),
    (12, 4, 'Carte jaune','Ouvre la porte jaune',NULL);

/* PROFIL UTILISATEURS */
INSERT INTO `profil_utilisateur` 
    (`id`,`libelle`) 
VALUES 
    (3,'Retraités'),
    (4,'Etudiants'),
    (6,'Alumnis'),
    (7,'Conjoints'),
    (8,'Personnels');

/* NIVEAU SPORTIF */
INSERT INTO `niveau_sportif` 
    (`id`, `libelle`) 
VALUES
    (1, 'Débutant'),
    (2, 'Intermediaire'),
    (3, 'Expert');

/**** UTILISATEURS ET GROUPES ****/
INSERT INTO `utilisateur` 
    (`id`, `profil_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `matricule`, `numero_nfc`, `prenom`, `nom`, `sexe`, `adresse`, `code_postal`, `ville`, `date_naissance`, `telephone`) 
VALUES
    (2, 3, 'dtinseau', 'dtinseau', 'damien.tinseau@acatus.fr', 'damien.tinseau@acatus.fr', 1, NULL, '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq', NULL, NULL, NULL,'a:0:{}', NULL, NULL, 'damien', 'tinseau', NULL, NULL, NULL, NULL, NULL, NULL),
    (4, 8, 'dgueudre', 'dgueudre', 'davy.gueudre@acatus.fr', 'davy.gueudre@acatus.fr', 1, NULL, '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq', NULL, NULL, NULL, 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}', NULL, NULL, 'davy', 'gueudre', NULL, NULL, NULL, NULL, NULL, NULL),
    (5, 7, 'lpaumier', 'lpaumier', 'laura.paumier@acatus.fr', 'laura.paumier@acatus.fr', 1, NULL, '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq', NULL, NULL, NULL, 'a:0:{}', NULL, NULL, 'laura', 'paumier', NULL, NULL, NULL, NULL, NULL, NULL),
    (7, 4, 'ymaresse', 'ymaresse', 'yaelle.maresse@atimic.fr', 'yaelle.maresse@atimic.fr', 1, NULL, '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq', NULL, NULL, NULL, 'a:0:{}', NULL, NULL, 'Yaelle', 'Maresse', NULL, NULL, NULL, NULL, NULL, NULL),
    (6, 4, 'pjolivet', 'pjolivet', 'pierre.jolivet@atimic.fr', 'pierre.jolivet@atimic.fr', 1, NULL, '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq', NULL, NULL, NULL, 'a:0:{}', NULL, NULL, 'Pierre', 'Jolivet', NULL, NULL, NULL, NULL, NULL, NULL),
    (8,3,'encadrant1','encadrant1','laurent.outan@atimic.fr','laurent.outan@atimic.fr',1,NULL,'$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq',NULL,NULL,NULL,'a:0:{}',NULL,NULL,'Laurent','Outan',NULL,NULL,NULL,NULL,NULL,NULL),
    (9,3,'encadrant2','encadrant2','larry.golade@acatus.fr','larry.golade@acatus.fr',1,NULL,'$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq',NULL,NULL,NULL,'a:0:{}',NULL,NULL,'Larry','Golade',NULL,NULL,NULL,NULL,NULL,NULL),
    (10,3,'encadrant3','encadrant3','lea.taburin@acatus.fr','lea.taburin@acatus.fr',1,NULL,'$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq',NULL,NULL,NULL,'a:0:{}',NULL,NULL,'Léa','Taburin',NULL,NULL,NULL,NULL,NULL,NULL);
/* Mot de passe Admin123* */
UPDATE `utilisateur` SET password = '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq';

/* GROUPE */
INSERT INTO `groupe` 
    (`id`, `name`, `roles`) 
VALUES
(1, 'Gestionnaire d\'activité', 'a:11:{i:0;s:30:\"ROLE_GESTION_ACTIVITE_ECRITURE\";i:1;s:37:\"ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE\";i:2;s:37:\"ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE\";i:3;s:29:\"ROLE_GESTION_CRENEAU_ECRITURE\";i:4;s:39:\"ROLE_GESTION_PROFIL_UTILISATEUR_LECTURE\";i:5;s:26:\"ROLE_GESTION_TARIF_LECTURE\";i:6;s:26:\"ROLE_GESTION_TEXTE_LECTURE\";i:7;s:31:\"ROLE_GESTION_TRADUCTION_LECTURE\";i:8;s:24:\"ROLE_GESTION_LOG_LECTURE\";i:9;s:39:\"ROLE_GESTION_TYPE_AUTORISATION_ECRITURE\";i:10;s:35:\"ROLE_GESTION_TYPE_ACTIVITE_ECRITURE\";}'),
(2, 'Gestionnaire financier', 'a:12:{i:0;s:27:\"ROLE_GESTION_TARIF_ECRITURE\";i:1;s:29:\"ROLE_GESTION_ACTIVITE_LECTURE\";i:2;s:36:\"ROLE_GESTION_FORMAT_ACTIVITE_LECTURE\";i:3;s:36:\"ROLE_GESTION_CLASSE_ACTIVITE_LECTURE\";i:4;s:34:\"ROLE_GESTION_TYPE_ACTIVITE_LECTURE\";i:5;s:28:\"ROLE_GESTION_CRENEAU_LECTURE\";i:6;s:40:\"ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE\";i:7;s:39:\"ROLE_GESTION_TYPE_AUTORISATION_ECRITURE\";i:8;s:32:\"ROLE_GESTION_UTILISATEUR_LECTURE\";i:9;s:34:\"ROLE_GESTION_ETABLISSEMENT_LECTURE\";i:10;s:30:\"ROLE_GESTION_RESSOURCE_LECTURE\";i:11;s:24:\"ROLE_GESTION_LOG_LECTURE\";}'),
(3, 'Encadrant', 'a:7:{i:0;s:29:\"ROLE_GESTION_ACTIVITE_LECTURE\";i:1;s:36:\"ROLE_GESTION_CLASSE_ACTIVITE_LECTURE\";i:2;s:28:\"ROLE_GESTION_CRENEAU_LECTURE\";i:3;s:34:\"ROLE_GESTION_ETABLISSEMENT_LECTURE\";i:4;s:36:\"ROLE_GESTION_FORMAT_ACTIVITE_LECTURE\";i:5;s:24:\"ROLE_GESTION_LOG_LECTURE\";i:6;s:30:\"ROLE_GESTION_RESSOURCE_LECTURE\";}'),
(4, 'Administrateur', 'a:16:{i:0;s:30:\"ROLE_GESTION_ACTIVITE_ECRITURE\";i:1;s:37:\"ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE\";i:2;s:37:\"ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE\";i:3;s:35:\"ROLE_GESTION_TYPE_ACTIVITE_ECRITURE\";i:4;s:29:\"ROLE_GESTION_CRENEAU_ECRITURE\";i:5;s:27:\"ROLE_GESTION_TARIF_ECRITURE\";i:6;s:33:\"ROLE_GESTION_UTILISATEUR_ECRITURE\";i:7;s:39:\"ROLE_GESTION_TYPE_AUTORISATION_ECRITURE\";i:8;s:31:\"ROLE_GESTION_RESSOURCE_ECRITURE\";i:9;s:35:\"ROLE_GESTION_ETABLISSEMENT_ECRITURE\";i:10;s:40:\"ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE\";i:11;s:27:\"ROLE_GESTION_TEXTE_ECRITURE\";i:12;s:32:\"ROLE_GESTION_TRADUCTION_ECRITURE\";i:13;s:24:\"ROLE_GESTION_LOG_LECTURE\";i:14;s:28:\"ROLE_GESTION_GROUPE_ECRITURE\";i:15;s:31:\"ROLE_GESTION_IMAGEFOND_ECRITURE\";}');



/* JOINTURE UTILISATEUR & GROUPES */
INSERT INTO `utilisateur_groupe` 
    (`utilisateur_id`, `groupe_id`) 
VALUES
    (2, 1),
    (4, 4),
    (5, 2),
    (6, 4),
    (8, 3),
    (9, 3),
    (10, 3);

/* JOINTURE TARIF & PROFIL */
INSERT INTO `montant_tarif_profil_utilisateur` 
    (`id`,`montant`,`tarif_id`,`profil_id`) 
VALUES 
    -- Tarif 1
    (25,20,15,3),
    (26,30,15,4),
    (27,200,15,6),
    (28,60,15,7),
    (29,40,15,8),
    -- Tarif 2
    (40,35,18,3),
    (41,15,18,4),
    (42,25,18,6),
    (43,25,18,7),
    (44,20,18,8),
    -- Tarif 3
    (1,30,25,3),
    (2,10,25,4),
    (3,20,25,6),
    (4,20,25,7),
    (5,15,25,8);

/* ACTIVTTE */
INSERT INTO `activite`
    (`id`,`classe_activite_id`, `libelle`, `description`,`image`) 
VALUES 
    (1, 4,'BasketBall','Mettre un ballon dans un panier. Si c\'est loin, c\'est fait 3 points !! \r\nYoupi !!\r\nTu peux aussi faire des dunks. c\'est beau les dunks mais ça fait que 2 points.','0b8c0d2dd7889388e2b8e62e81fe64769b343dc1.jpg'),
    (2, 5,'Natation','Atteindre l\'autre bord sans se noyer','51cce86d82d7c283f9dfa8ed66978d71c5648916.jpg') ,
    (3, 1,'Musculation','Soulevez des choses, courez sur d\'autres choses','28133a34fe3c15c3266708089295be66dae75c7d.jpg') ,
    (4, 2, 'Ski','Glissez,... ou marchez c\'est au choix.','28133a34fe3c15c7866708089295be66dae68c7d.jpg') ,
    (5, 3, 'Tennis','Tapez dans la balle, très fort !','28133a35ze3c15c7866708071295be66dae68c7d.jpg');

/* FORmAT D'ACTIVITE */
INSERT INTO `format_activite` 
    (`id`, `activite_id`, `tarif_id`, `libelle`, `description`, `lien_html`, `lien_pdf`, `date_debut_effective`, `date_fin_effective`, `date_debut_inscription`, `date_fin_inscription`, `image`, `updated_at`, `est_payant`, `est_encadre`, `format`,`type_autorisation_id`)    
VALUES
    (1, 1, 15, 'Basketball en salle', 'Si vous voulez jouer dehors!', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00','image1.jpg','2018-12-25 00:00:00',1,0,'FormatAvecCreneau',NULL),
    (2, 1, 15, 'BasketBall en extérieur','Si le soleil vous effraie',NULL,NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00','image2.jpg','2018-12-25 00:00:00',1,0,'FormatAvecCreneau',NULL),
    (3, 2, 15, 'Natation Crawl', 'Apprenez le Crawl', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00','image3.jpg','2018-12-25 00:00:00',1,0,'FormatAvecCreneau',NULL),
    (4, 3, 15, 'Musculation', 'Musculation', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00','image4.png','2018-12-25 00:00:00',1,0,'FormatSimple',NULL),
    (5, 4, 15, 'Sorti Ski', 'Ski', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2014-01-01 00:00:00','image5.jpg','2018-12-25 00:00:00',1,0,'FormatSimple',NULL),
    (10, 3, 25, 'Carte Musculation','Accès Salle 1 & 2',NULL,NULL,'2018-09-01 00:00:00','2019-07-01 00:00:00','2014-01-01 00:00:00', '2014-01-01 00:00:00','image6.jpg','2018-12-25 00:00:00',1,0,'FormatAchatCarte',13),
    (11, 5, 25, 'Carte Jaune','Ouvre la porte jaune',NULL,NULL,'2018-09-01 00:00:00','2019-07-01 00:00:00','2014-01-01 00:00:00', '2014-01-01 00:00:00','image7.jpg','2018-12-25 00:00:00',1,0,'FormatAchatCarte',12);

/* JOINTURE FORMAT & NIVEAU */
INSERT INTO `format_activite_niveau_sportif` 
    (`format_activite_id`, `niveau_sportif_id`) 
VALUES 
    (1, 1),
    (1, 2),
    (2, 2),
    (2, 3),
    (3, 1),
    (3, 2),
    (3, 3),
    (4, 1),
    (4, 2),
    (4, 3);

/* TRADUCTION */
INSERT INTO `ext_translations` 
    (`id`,`locale`,`object_class`,`field`,`foreign_key`,`content`) 
VALUES 
    (1,'en','UcaBundle\\Entity\\ClasseActivite','libelle','1','Interior'),
    (2,'en','UcaBundle\\Entity\\ClasseActivite','libelle','2','Exterior'),
    (3,'en','UcaBundle\\Entity\\ClasseActivite','libelle','3','Raquet sport '),
    (4,'en','UcaBundle\\Entity\\ClasseActivite','libelle','4','Ball games'),
    (5,'en','UcaBundle\\Entity\\ClasseActivite','description','4','Basketball, Football...'),
    (6,'en','UcaBundle\\Entity\\ClasseActivite','libelle','5','Water sports'),
    (7,'en','UcaBundle\\Entity\\ClasseActivite','description','5','Swimming...'),
    (8,'en','UcaBundle\\Entity\\TypeActivite','libelle','1','Sport En'),
    (9,'en','UcaBundle\\Entity\\TypeActivite','libelle','2','Culture En'),
    (10,'en','UcaBundle\\Entity\\ProfilUtilisateur','libelle','3','Retiree'),
    (11,'en','UcaBundle\\Entity\\ProfilUtilisateur','libelle','4','Student'),
    (12,'en','UcaBundle\\Entity\\ProfilUtilisateur','libelle','6','Alumnis En'),
    (13,'en','UcaBundle\\Entity\\ProfilUtilisateur','libelle','7','Partners'),
    (14,'en','UcaBundle\\Entity\\ProfilUtilisateur','libelle','8','Staff Members'),
    (15,'en','UcaBundle\\Entity\\Jour','libelle','1','Monday'),
    (16,'en','UcaBundle\\Entity\\Jour','libelle','2','Tuesday'),
    (17,'en','UcaBundle\\Entity\\Jour','libelle','3','Wednesday'),
    (18,'en','UcaBundle\\Entity\\Jour','libelle','4','Thurday'),
    (19,'en','UcaBundle\\Entity\\Jour','libelle','5','Friday'),
    (20,'en','UcaBundle\\Entity\\Jour','libelle','6','Saturday'),
    (21,'en','UcaBundle\\Entity\\Jour','libelle','7','Sunday');

/* TEXTE */
INSERT INTO `texte` 
    (`id`, `emplacement`, `titre`, `texte`, `mobile`) 
VALUES
    (1, 'Renseignements', 'MAJ', '<p>Et licet quocumque oculos flexeris feminas adfatim multas spectare cirratas, quibus, si nupsissent, per aetatem ter iam nixus poterat suppetere liberorum, ad usque taedium pedibus pavimenta tergentes iactari volucriter gyris, dum exprimunt innumera simulacra, quae finxere fabulae theatrales.</p>', 1),
    (2, 'Accueil', 'Qwerty', 'Proinde <b>die</b> funestis interrogationibus praestituto imaginarius iudex equitum resedit magister adhibitis aliis iam quae essent agenda praedoctis, et adsistebant hinc inde notarii, quid quaesitum esset, quidve responsum, cursim ad Caesarem perferentes, cuius imperio truci, stimulis reginae exsertantis aurem subinde per aulaeum, nec diluere obiecta permissi nec defensi periere conplures.', 1),
    (3, 'Evenements', 'Azerty', '<p style=\"text-align: right;\"><span style=\"color: #5e5737; font-size: 20px;\"><strong>Post</strong> hanc adclinis Libano monti Phoenice, regio plena <em><strong>gratiarum</strong> </em>et venustatis, urbibus decorata magnis et pulchris; in quibus amoenitate celebritateque <strong>nominum</strong> Tyros excellit, Sidon et Berytus isdemque pares Emissa et Damascus saeculis condita priscis.</span></p>', 0),
    (4, 'Activite', 'Sports', '<p>azertyuiopqsdfghklm azertyuiop</p>', 1),
    (5, 'Inscription', 'Vous êtes étudiant à l\'Université Côte d\'Azur ?', '<p>Un compte a d&eacute;j&agrave; &eacute;t&eacute; cr&eacute;&eacute; pour vous. Pour vous connecter et vous inscrire aux activit&eacute;s, munissez-vous simplement de votre adresse mail et du mot de passe utilis&eacute;s lors de votre inscription au campus universitaire. Apr&egrave;s vous &ecirc;tre acquitt&eacute; de la cotisation sportive annuelle, vous pourrez vous inscrire aux activit&eacute;s sportives propos&eacute;es par le campus, parmi une liste de 70 activit&eacute;s !</p>\r\n\r\n<p>&nbsp;</p>', 0),
    (6, 'inscription enregistrement', 'Vous ne faîtes pas parti de l\'université et souhaitez vous inscrire aux activités ?', '', 0);

/* Image de fond */
INSERT INTO `image_fond` (`id`, `emplacement`, `titre`, `image`, `updated_at`) VALUES
    (1, 'Page principale', 'Test', '5d07b1f8ddab5681205102.jpg', '2019-06-17 15:30:00'),
    (2, 'quelque part', 'Image de Fond', '5d07b2fd880e2862047916.png', '2019-06-17 15:34:21'),
    (3, 'dans le puit', "Affichage d'un bug", '5d07b30c86e48098508917.PNG', '2019-06-17 15:34:36');