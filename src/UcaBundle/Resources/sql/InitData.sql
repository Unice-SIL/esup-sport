/* SET SQL_SAFE_UPDATES = 0; */

TRUNCATE TABLE `dhtmlx_date`;

DELETE FROM `article`;
DELETE FROM `actualite`;

DELETE FROM `ext_translations`;
DELETE FROM `format_activite_niveau_sportif`;
DELETE FROM `format_activite_lieu`;
DELETE FROM `format_activite_profil_utilisateur`;
DELETE FROM `creneau_profil_utilisateur`;
DELETE FROM `inscription`;
DELETE FROM `creneau`;
DELETE FROM `format_activite`;
DELETE FROM `activite` ;

DELETE FROM `panier`;
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
(1, 'Sport'),
(7, 'Culture');

/* TARIF */
INSERT INTO `tarif` (`id`, `libelle`, `modification_montants`) VALUES
(15, 'Cotisation sportive', ''),
(25, 'Carte Tennis', ''),
(33, 'Tennis - cours encadré', ''),
(34, 'Tennis - Créneaux du matin', ''),
(35, 'Tennis - Créneaux du soir', ''),
(36, 'Gratuit', ''),
(37, 'Location raquette Tennis', '');

/* ETABLISSEMENTS */
INSERT INTO `etablissement` (`id`,`code`,`libelle`,`adresse`,`code_postal`,`ville`,`image`,`updated_at`,`email`,`telephone`,`horaires_ouverture`) VALUES (1,'CSU VALROSE (Campus Sciences)','Campus Valrose','5 rue de la rose','06000','Nice','5d19d834e3029006182214.jpg','2019-07-01 09:53:56','contact@valrose.fr','0494262524','Lundi: 14h - 18h \r\nMardi: 9h - 18h \r\nMercredi: 9h - 12h \r\nJeudi: 9h - 18h \r\nVendredi: 9h - 12h \r\nSamedi: Fermé\r\nDimanche: Fermé');
INSERT INTO `etablissement` (`id`,`code`,`libelle`,`adresse`,`code_postal`,`ville`,`image`,`updated_at`,`email`,`telephone`,`horaires_ouverture`) VALUES (2,'CSU CARLONE (Campus Carlone)','Campus Carlone','10 Boulevard Requin','06000','Nice','5d0b87826e25d101624824.jpg',NULL,'contact@carlone.fr',NULL,'Lundi: 10h - 17h \r\nMardi: 10h - 17h \r\nMercredi: 10h - 17h \r\nJeudi: 10h - 17h \r\nVendredi: 10h - 17h \r\nSamedi: 9h - 12h \r\nDimanche: Fermé');
INSERT INTO `etablissement` (`id`,`code`,`libelle`,`adresse`,`code_postal`,`ville`,`image`,`updated_at`,`email`,`telephone`,`horaires_ouverture`) VALUES (6,'CSU TROTABAS (Campus droit)','Campus Trotabas','31 rue des bouleaux','46420','Saint Germain des Roses','5d14831cab5da631509376.jpg','2019-06-27 08:49:32','dgueudre@gmail.com','0672707765','Lundi: 10h - 17h \r\nMardi: 10h - 17h \r\nMercredi: 10h - 17h \r\nJeudi: 10h - 17h \r\nVendredi: 10h - 17h \r\nSamedi: 9h - 12h \r\nDimanche: Fermé');

/* RESSOURCES */
INSERT INTO `ressource` (`id`, `etablissement_id`, `tarif_id`, `libelle`, `description`, `source_referentiel`, `image`, `updated_at`, `format`, `nomenclature_rus`, `superficie`, `capacite`, `latitude`, `longitude`, `adresse`, `code_postal`, `ville`, `quantite_disponible`) VALUES
(27, 1, NULL, 'Terrain Tennis 1', 'Terrain tennis terre battue  1', 0, '5d11dd00a35b4020691676.png', '2019-06-25 08:36:16', 'Lieu', NULL, NULL, NULL, NULL, NULL, '45 rue des blanc bouleaux', '85000', 'Saint Brien des Vosges', NULL),
(28, 1, NULL, 'Terrain Tennis 2', 'Terrain tennis terre battue  2', 0, '5d11dd1ad8e3d223175729.png', '2019-06-25 08:36:42', 'Lieu', NULL, NULL, NULL, NULL, NULL, '45 rue des blanc bouleaux', '85000', 'Saint Brien des Vosges', NULL),
(29, NULL, 37, 'Raquettes de tennis', NULL, 0, '5d11e081d2eee983315244.png', '2019-06-25 08:51:13', 'Materiel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20),
(30, 1, NULL, 'SALLE DANSE Niveau 1', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0V010001', '228', NULL, '21.259', '21.273', NULL, NULL, NULL, NULL),
(31, 1, NULL, 'SALLE DE COMBAT Niveau 1', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0V010002', '229', NULL, '21.260', '21.274', NULL, NULL, NULL, NULL),
(32, 1, NULL, 'GYMNASE A RDC', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0V010003', '340', NULL, '21.261', '21.275', NULL, NULL, NULL, NULL),
(33, 1, NULL, 'MUR ESCALADE - GYMNASE C Niveau 3 ', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0V030001', NULL, NULL, '21.263', '21.277', NULL, NULL, NULL, NULL),
(34, 1, NULL, 'SALLE DE PAN (escalade)  Niveau 1', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0V010007', '22', NULL, '21.264', '21.278', NULL, NULL, NULL, NULL),
(35, 1, NULL, 'SALLE DE MUSCULATION Niveau 1', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0V010009', '128', NULL, '21.265', '21.279', NULL, NULL, NULL, NULL),
(36, 1, NULL, 'SALLE DE COURS FERNAND HALEC Niveau 2', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0V020001', '42', NULL, '21.266', '21.280', NULL, NULL, NULL, NULL),
(37, 1, NULL, 'TERRAIN TENNIS', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0VTT01', '261', NULL, '21.267', '21.281', NULL, NULL, NULL, NULL),
(38, 1, NULL, 'TERRAIN TENNIS', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0VTT02', '261', NULL, '21.268', '21.282', NULL, NULL, NULL, NULL),
(39, 1, NULL, 'TERRAIN TENNIS', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0VTT0003', '261', NULL, '21.269', '21.283', NULL, NULL, NULL, NULL),
(40, 1, NULL, 'TERRAIN PADEL', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0VTP0001', '228', NULL, '21.270', '21.284', NULL, NULL, NULL, NULL),
(41, 1, NULL, 'TERRAIN PADEL', NULL, 1, 'no.png', NULL, 'Lieu', 'UNV0VTP0002', '228', NULL, '21.271', '21.285', NULL, NULL, NULL, NULL),
(42, 2, NULL, 'SALLE DANSE Niveau 4', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGP040002', '98', NULL, '21.272', '21.286', NULL, NULL, NULL, NULL),
(43, 2, NULL, 'GYMNASE  Niveau 0', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGPRC0006', '801', NULL, '21.273', '21.287', NULL, NULL, NULL, NULL),
(44, 2, NULL, 'SALLE DE MUSCULATION Niveau 1', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGP010009', '117', NULL, '21.274', '21.288', NULL, NULL, NULL, NULL),
(45, 2, NULL, 'SALLE DE COURS Niveau 4', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGP04', '34', NULL, '21.275', '21.289', NULL, NULL, NULL, NULL),
(46, 2, NULL, 'PISCINE COUVERTE', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGP030006', '382', NULL, '21.276', '21.290', NULL, NULL, NULL, NULL),
(47, 2, NULL, 'TERRAIN TENNIS FIELDING', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGPTT0001', '261', NULL, '21.277', '21.291', NULL, NULL, NULL, NULL),
(48, 2, NULL, 'TERRAIN TENNIS FIELDING', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGPTT0002', '261', NULL, '21.278', '21.292', NULL, NULL, NULL, NULL),
(49, 2, NULL, 'TERRAIN TENNIS FIELDING', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGPTT0003', '261', NULL, '21.279', '21.293', NULL, NULL, NULL, NULL),
(50, 2, NULL, 'TERRAIN TENNIS FIELDING', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGPTT0004', '261', NULL, '21.280', '21.294', NULL, NULL, NULL, NULL),
(51, 2, NULL, 'TERRAIN TENNIS FIELDING', NULL, 1, 'no.png', NULL, 'Lieu', 'UNCGPTT0005', '261', NULL, '', '', NULL, NULL, NULL, NULL),
(52, 6, NULL, 'SALLE DANSE Niveau 0', NULL, 1, 'no.png', NULL, 'Lieu', 'UNT0DRCG002', '153', NULL, '21.282', '21.296', NULL, NULL, NULL, NULL),
(53, 6, NULL, 'SALLE DE COMBAT Niveau 0', NULL, 1, 'no.png', NULL, 'Lieu', 'UNT0DRCG006', '151', NULL, '21.283', '21.297', NULL, NULL, NULL, NULL),
(54, 6, NULL, 'GYMNASE Niveau 1 ', NULL, 1, 'no.png', NULL, 'Lieu', 'UNT0D01G101', '600', NULL, '21.284', '21.298', NULL, NULL, NULL, NULL),
(55, 6, NULL, 'SALLE DE MUSCULATION Niveau 0', NULL, 1, 'no.png', NULL, 'Lieu', 'UNT0DRCG007', '153', NULL, '21.285', '21.299', NULL, NULL, NULL, NULL),
(56, NULL, NULL, 'Tabouret', NULL, 0, '5d1483b86afee770335484.jpg', '2019-06-27 08:52:08', 'Materiel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10);

/* CLASSE D'ACTIVITE */
INSERT INTO `classe_activite` (`id`, `type_activite_id`, `libelle`, `image`, `updated_at`) VALUES
(1, 1, 'Sport en salle', '5d1a1f39599de423990805.jpg', '2019-07-01 14:56:57'),
(2, 1, 'Sport de plein air', '5d1a1cb3c66d9751043768.jpg', '2019-07-01 14:46:11'),
(3, 1, 'Sport de raquette', '5d1a1f4f490be710077896.jpg', '2019-07-01 14:57:19'),
(5, 1, 'Sport d''eau', '5d1a1f613759a618231719.jpg', '2019-07-01 14:57:37'),
(10, 1, 'Sport collectif', '5d1a2011554f2467584368.jpg', '2019-07-01 15:00:33'),
(11, 7, 'Danse', '5d1a204b3c170688671051.jpg', '2019-07-01 15:01:31');


/* COMPORTEMENT */ 
INSERT INTO `comportement_autorisation` (`id`, `libelle`, `code_comportement`) VALUES
(1, 'Cotisation', 'cotisation'),
(2, 'Justificatif à fournir', 'justificatif'),
(3, 'Case à cocher', 'case'),
(4, 'Achat de Carte', 'carte'),
(5, 'Validation par un encadrant', 'validationencadrant');

/* TYPE D'AUTORISATION */
INSERT INTO `type_autorisation` (`id`, `tarif_id`, `comportement_id`, `libelle`, `informations_complementaires`) VALUES
(2, 15, 1, 'Cotisation sportive', NULL),
(3, NULL, 3, 'Certificat médical', 'Je certifie être en possession d\'un certificat médical pour participer à cette activité'),
(4, NULL, 2, 'Autorisation plongée', NULL),
(5, NULL, 2, 'Autorisation escalade', NULL),
(12, NULL, 4, 'Carte Piscine', NULL),
(13, 25, 4, 'Carte Musculation', NULL),
(14, NULL, 4, 'Carte Tennis', NULL),
(16, NULL, 3, 'Aptitude à la natation', 'Je certifie que je sais nager'),
(17, NULL, 2, 'Autorisation Tennis', NULL),
(18, NULL, 5, 'Validation par un encadrant', 'Votre inscription doit être validée par un encadrant avant de pouvoir être ajoutée au panier');

/* PROFIL UTILISATEURS */
INSERT INTO `profil_utilisateur` (`id`, `libelle`) VALUES
(3, 'Retraités'),
(4, 'Etudiants'),
(6, 'Alumnis'),
(7, 'Conjoints'),
(8, 'Personnels');

/* NIVEAU SPORTIF */
INSERT INTO `niveau_sportif` (`id`, `libelle`) VALUES
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
INSERT INTO `groupe` (`id`, `name`, `roles`) VALUES
(1, 'Gestionnaire d\'activité', 'a:11:{i:0;s:30:\"ROLE_GESTION_ACTIVITE_ECRITURE\";i:1;s:37:\"ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE\";i:2;s:37:\"ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE\";i:3;s:29:\"ROLE_GESTION_CRENEAU_ECRITURE\";i:4;s:39:\"ROLE_GESTION_PROFIL_UTILISATEUR_LECTURE\";i:5;s:26:\"ROLE_GESTION_TARIF_LECTURE\";i:6;s:26:\"ROLE_GESTION_TEXTE_LECTURE\";i:7;s:31:\"ROLE_GESTION_TRADUCTION_LECTURE\";i:8;s:24:\"ROLE_GESTION_LOG_LECTURE\";i:9;s:39:\"ROLE_GESTION_TYPE_AUTORISATION_ECRITURE\";i:10;s:35:\"ROLE_GESTION_TYPE_ACTIVITE_ECRITURE\";}'),
(2, 'Gestionnaire financier', 'a:12:{i:0;s:27:\"ROLE_GESTION_TARIF_ECRITURE\";i:1;s:29:\"ROLE_GESTION_ACTIVITE_LECTURE\";i:2;s:36:\"ROLE_GESTION_FORMAT_ACTIVITE_LECTURE\";i:3;s:36:\"ROLE_GESTION_CLASSE_ACTIVITE_LECTURE\";i:4;s:34:\"ROLE_GESTION_TYPE_ACTIVITE_LECTURE\";i:5;s:28:\"ROLE_GESTION_CRENEAU_LECTURE\";i:6;s:40:\"ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE\";i:7;s:39:\"ROLE_GESTION_TYPE_AUTORISATION_ECRITURE\";i:8;s:32:\"ROLE_GESTION_UTILISATEUR_LECTURE\";i:9;s:34:\"ROLE_GESTION_ETABLISSEMENT_LECTURE\";i:10;s:30:\"ROLE_GESTION_RESSOURCE_LECTURE\";i:11;s:24:\"ROLE_GESTION_LOG_LECTURE\";}'),
(3, 'Encadrant', 'a:7:{i:0;s:29:\"ROLE_GESTION_ACTIVITE_LECTURE\";i:1;s:36:\"ROLE_GESTION_CLASSE_ACTIVITE_LECTURE\";i:2;s:28:\"ROLE_GESTION_CRENEAU_LECTURE\";i:3;s:34:\"ROLE_GESTION_ETABLISSEMENT_LECTURE\";i:4;s:36:\"ROLE_GESTION_FORMAT_ACTIVITE_LECTURE\";i:5;s:24:\"ROLE_GESTION_LOG_LECTURE\";i:6;s:30:\"ROLE_GESTION_RESSOURCE_LECTURE\";}'),
(4, 'Administrateur', 'a:17:{i:0;s:30:\"ROLE_GESTION_ACTIVITE_ECRITURE\";i:1;s:37:\"ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE\";i:2;s:37:\"ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE\";i:3;s:35:\"ROLE_GESTION_TYPE_ACTIVITE_ECRITURE\";i:4;s:29:\"ROLE_GESTION_CRENEAU_ECRITURE\";i:5;s:27:\"ROLE_GESTION_TARIF_ECRITURE\";i:6;s:33:\"ROLE_GESTION_UTILISATEUR_ECRITURE\";i:7;s:39:\"ROLE_GESTION_TYPE_AUTORISATION_ECRITURE\";i:8;s:31:\"ROLE_GESTION_RESSOURCE_ECRITURE\";i:9;s:35:\"ROLE_GESTION_ETABLISSEMENT_ECRITURE\";i:10;s:40:\"ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE\";i:11;s:27:\"ROLE_GESTION_TEXTE_ECRITURE\";i:12;s:32:\"ROLE_GESTION_TRADUCTION_ECRITURE\";i:13;s:24:\"ROLE_GESTION_LOG_LECTURE\";i:14;s:28:\"ROLE_GESTION_GROUPE_ECRITURE\";i:15;s:31:\"ROLE_GESTION_IMAGEFOND_ECRITURE\";i:16;s:31:\"ROLE_GESTION_ACTUALITE_ECRITURE\";}');


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
INSERT INTO `montant_tarif_profil_utilisateur` (`id`, `tarif_id`, `profil_id`, `montant`) VALUES
(1, 25, 3, '30'),
(2, 25, 4, '10'),
(3, 25, 6, '20'),
(4, 25, 7, '20'),
(5, 25, 8, '15'),
(25, 15, 3, '20'),
(26, 15, 4, '30'),
(27, 15, 6, '200'),
(28, 15, 7, '60'),
(29, 15, 8, '40'),
(85, 33, 3, '70'),
(86, 33, 4, '50'),
(87, 33, 6, '60'),
(88, 33, 7, '70'),
(89, 33, 8, '50'),
(90, 34, 3, '8'),
(91, 34, 4, '8'),
(92, 34, 6, '8'),
(93, 34, 7, '8'),
(94, 34, 8, '8'),
(95, 35, 3, '10'),
(96, 35, 4, '10'),
(97, 35, 6, '10'),
(98, 35, 7, '10'),
(99, 35, 8, '10'),
(100, 36, 3, '0'),
(101, 36, 4, '0'),
(102, 36, 6, '0'),
(103, 36, 7, '0'),
(104, 36, 8, '0'),
(105, 37, 3, '5'),
(106, 37, 4, '5'),
(107, 37, 6, '5'),
(108, 37, 7, '5'),
(109, 37, 8, '5');

/* ACTIVTTE */
INSERT INTO `activite` (`id`, `classe_activite_id`, `libelle`, `description`, `image`, `updated_at`) VALUES
(1, 10, 'Basketball', 'Pratique du Basketball', '5d1a20b7b61a5920647194.jpg', '2019-07-01 15:03:19'),
(2, 5, 'Natation', 'Pratique de la natation', '5d1a20c5d32e5054082372.jpg', '2019-07-01 15:03:33'),
(3, 1, 'Musculation', 'Pratique de la musculation', '5d1a20f45d129119818620.jpg', '2019-07-01 15:04:20'),
(4, 2, 'Ski', 'Pratique du ski', '5d1a211a6d005260302725.jpg', '2019-07-01 15:04:58'),
(5, 3, 'Tennis', 'Pratique du Tennis', '5d25a50a7b9ad740379664.jpg', '2019-07-01 15:05:39');

/* FORmAT D'ACTIVITE */
INSERT INTO `format_activite` (`id`, `activite_id`, `tarif_id`, `carte_id`, `libelle`, `description`, `lien_html`, `lien_pdf`, `date_debut_publication`, `date_fin_publication`, `date_debut_inscription`, `date_fin_inscription`, `date_debut_effective`, `date_fin_effective`, `image`, `updated_at`, `est_payant`, `est_encadre`, `capacite`, `statut`, `format`, `promouvoir`) VALUES
(1, 1, NULL, NULL, 'Cours de Basketball Créneau du Lundi', 'Cours pour niveaux débutants et intermédiaires', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-01-01 00:00:00', '2020-01-01 00:00:00', '2019-01-01 00:00:00', '2020-01-01 00:00:00', '5d1a2165447de335162135.png', '2019-07-01 15:06:13', 0, 0, 0, 0, 'FormatAvecCreneau', NULL),
(11, 5, 15, 14, 'Achat carte tennis', 'Permet d\'accéder à la réservation des cours de tennis', NULL, NULL, '2019-01-01 00:00:00', '2020-04-01 00:00:00', '2018-01-01 00:00:00', '2019-01-01 00:00:00', '2018-09-01 00:00:00', '2019-07-01 00:00:00', '5d11dd9d8246f405176618.png', '2019-06-25 08:38:53', 0, 0, 0, 1, 'FormatAchatCarte', NULL),
(21, 5, NULL, NULL, 'Cours débutant', 'Cours débutant', NULL, NULL, '2019-01-01 00:00:00', '2020-04-01 00:00:00', '2019-06-25 00:00:00', '2019-10-25 00:00:00', '2019-10-25 00:00:00', '2020-06-25 00:00:00', '5d11dde570130751210426.png', '2019-06-25 08:40:05', 0, 1, 0, 1, 'FormatAvecCreneau', NULL),
(22, 5, NULL, NULL, 'Reservation du terrain n°1', 'Reservation du terrain n°1', NULL, NULL, '2019-01-01 00:00:00', '2020-04-01 00:00:00', '2019-06-25 00:00:00', '2019-12-25 00:00:00', '2019-12-25 00:00:00', '2020-06-25 00:00:00', '5d11de169327e180598553.jpg', '2019-06-25 08:40:54', 0, 0, 0, 1, 'FormatAvecReservation', NULL),
(23, 5, NULL, NULL, 'Tournoi double', 'Tournoi de tennis du 25/12/2019', NULL, NULL, '2019-01-01 00:00:00', '2020-04-01 00:00:00', '2019-06-25 00:00:00', '2019-12-25 00:00:00', '2019-12-25 00:00:00', '2019-12-25 00:00:00', '5d11de6591d6c476523191.png', '2019-06-25 08:42:13', 0, 0, 0, 1, 'FormatSimple', 0),
(24, 4, 25, 12, 'Achat carte de ski', 'Achat carte de ski', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-06-27 00:00:00', '2020-06-27 00:00:00', '2019-06-27 00:00:00', '2020-06-27 00:00:00', '5d1a21b52f947701122218.png', '2019-07-01 15:07:33', 1, 0, 0, 0, 'FormatAchatCarte', NULL),
(25, 2, NULL, NULL, 'Concours piscine Juin 2019', 'Concours piscine Juin 2019', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-05-01 00:00:00', '2019-06-01 00:00:00', '2019-06-01 00:00:00', '2019-07-01 00:00:00', '5d1a218ebcd6d020102633.png', '2019-07-01 15:06:54', 0, 0, 0, 0, 'FormatSimple', 1),
(26, 2, NULL, NULL, 'Concours piscine Juillet 2019', 'Concours piscine Juillet 2019', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-06-01 00:00:00', '2019-07-01 00:00:00', '2019-07-01 00:00:00', '2019-08-01 00:00:00', '5d1a218ebcd6d020102633.png', '2019-07-01 15:06:54', 0, 0, 0, 0, 'FormatSimple', 1),
(27, 2, NULL, NULL, 'Concours piscine Aout 2019', 'Concours piscine Aout 2019', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-07-01 00:00:00', '2019-08-01 00:00:00', '2019-08-01 00:00:00', '2019-09-01 00:00:00', '5d1a218ebcd6d020102633.png', '2019-07-01 15:06:54', 0, 0, 0, 0, 'FormatSimple', 1),
(28, 2, NULL, NULL, 'Concours piscine Septembre 2019', 'Concours piscine Septembre 2019', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-08-01 00:00:00', '2019-09-01 00:00:00', '2019-09-01 00:00:00', '2019-10-01 00:00:00', '5d1a218ebcd6d020102633.png', '2019-07-01 15:06:54', 0, 0, 0, 0, 'FormatSimple', 1),
(29, 2, NULL, NULL, 'Concours piscine Octobre 2019', 'Concours piscine Octobre 2019', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-09-01 00:00:00', '2019-10-01 00:00:00', '2019-10-01 00:00:00', '2019-11-01 00:00:00', '5d1a218ebcd6d020102633.png', '2019-07-01 15:06:54', 0, 0, 0, 0, 'FormatSimple', 1),
(34, 5, NULL, 14, 'Carte tennis premium', 'Carte tennis premium', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00', '2019-07-08 11:59:00', '2019-07-08 11:59:00', '2019-07-08 11:59:00', '2019-07-08 11:59:00', '5d23305feac37998388004.png', '2019-07-08 12:00:31', 0, 0, 30, 1, 'FormatAchatCarte', NULL),
(35, 1, NULL, NULL, 'Cours Basket débutant', 'Cours pour débutants', NULL, NULL, '2019-10-01 00:00:00', '2020-04-01 00:00:00', '2019-09-01 00:00:00', '2019-10-01 00:00:00', '2019-10-01 00:00:00', '2019-11-01 00:00:00', '5d1a2165447de335162135.png', '2019-07-08 12:00:31', 0, 0, 20, 0, 'FormatAvecCreneau', NULL);

/* JOINTURE FORMAT & NIVEAU */
INSERT INTO `format_activite_niveau_sportif` (`format_activite_id`, `niveau_sportif_id`) VALUES
(1, 1),
(1, 2),
(11, 1),
(11, 2),
(11, 3),
(21, 1),
(21, 2),
(21, 3),
(22, 1),
(22, 2),
(22, 3),
(23, 1),
(23, 2),
(23, 3),
(24, 1),
(24, 2),
(24, 3),
(25, 1),
(25, 2),
(25, 3);

INSERT INTO `format_activite_lieu` (`format_activite_id`, `lieu_id`) VALUES
(21, 27);

INSERT INTO `format_activite_profil_utilisateur` (`format_activite_id`, `profil_utilisateur_id`) VALUES
(21, 3),
(21, 4),
(21, 6),
(21, 7),
(21, 8);

INSERT INTO `format_activite_type_autorisation` (`format_activite_id`, `type_autorisation_id`) VALUES
(21, 2),
(21, 3),
(21, 14),
(21, 17),
(21, 18);

INSERT INTO `format_activite_utilisateur` (`format_activite_id`, `utilisateur_id`) VALUES
(21, 8),
(21, 9);

/* TEXTE */
INSERT INTO `texte` 
    (`id`, `emplacement`, `titre`, `texte`, `mobile`) 
VALUES
    (1, 'Renseignements', 'MAJ', '<p>Et licet quocumque oculos flexeris feminas adfatim multas spectare cirratas, quibus, si nupsissent, per aetatem ter iam nixus poterat suppetere liberorum, ad usque taedium pedibus pavimenta tergentes iactari volucriter gyris, dum exprimunt innumera simulacra, quae finxere fabulae theatrales.</p>', 1),
    (2, 'Accueil', 'Qwerty', 'Proinde <b>die</b> funestis interrogationibus praestituto imaginarius iudex equitum resedit magister adhibitis aliis iam quae essent agenda praedoctis, et adsistebant hinc inde notarii, quid quaesitum esset, quidve responsum, cursim ad Caesarem perferentes, cuius imperio truci, stimulis reginae exsertantis aurem subinde per aulaeum, nec diluere obiecta permissi nec defensi periere conplures.', 1),
    (3, 'Evenements', 'Azerty', '<p style=\"text-align: right;\"><span style=\"color: #5e5737; font-size: 20px;\"><strong>Post</strong> hanc adclinis Libano monti Phoenice, regio plena <em><strong>gratiarum</strong> </em>et venustatis, urbibus decorata magnis et pulchris; in quibus amoenitate celebritateque <strong>nominum</strong> Tyros excellit, Sidon et Berytus isdemque pares Emissa et Damascus saeculis condita priscis.</span></p>', 0),
    (4, 'Activite', 'Sports', '<p>azertyuiopqsdfghklm azertyuiop</p>', 1),
    (5, 'Inscription', 'Vous êtes étudiant à l\'Université Côte d\'Azur ?', '<p>Un compte a d&eacute;j&agrave; &eacute;t&eacute; cr&eacute;&eacute; pour vous. Pour vous connecter et vous inscrire aux activit&eacute;s, munissez-vous simplement de votre adresse mail et du mot de passe utilis&eacute;s lors de votre inscription au campus universitaire. Apr&egrave;s vous &ecirc;tre acquitt&eacute; de la cotisation sportive annuelle, vous pourrez vous inscrire aux activit&eacute;s sportives propos&eacute;es par le campus, parmi une liste de 70 activit&eacute;s !</p>\r\n\r\n<p>&nbsp;</p>', 0),
    (6, 'inscription enregistrement', 'Vous ne faîtes pas parti de l\'université et souhaitez vous inscrire aux activités ?', '', 0),
    (7, 'Sport haut niveau introduction', '', 'Université Côte d\'Azur mène une politique volontariste d’accueil et de soutien à la réussite des sportifs et sportives de haut niveau universitaire (SHNU). Les étudiantes et étudiants inscrits au sein de l’Établissement peuvent obtenir sur demande, un statut \"Sportif de Haut Niveau Universitaire\" (SHNU) et bénéficier d’un accompagnement personnalisé.', 0),
    (8, 'Sport haut niveau encadrant', 'ENCADREMENT', '<h2>Les r&eacute;f&eacute;rents SHNU par composante</h2>\r\n\r\n<p>Ils sont le suivi et l&rsquo;interlocuteur direct de l&rsquo;&eacute;tudiant athl&egrave;te, v&eacute;ritable courroie de transmission avec l&rsquo;&eacute;quipe p&eacute;dagogique universitaire d&rsquo;une part, le charg&eacute; de mission Haut niveau de l&rsquo;&eacute;tablissement et le club de l&rsquo;&eacute;tudiant athl&egrave;te d&rsquo;autre part.</p>\r\n\r\n<p>Droit : marc.peltier@unice.fr</p>\r\n\r\n<p>ESPE : isabelle.schnenhenz@unice.fr</p>\r\n\r\n<p>IAE : manuela.bardet@unice.fr</p>\r\n\r\n<p>ISEM : sandye.gloria-palermo@unice.fr</p>\r\n\r\n<p>IUT : le directeur d&rsquo;&eacute;tudes de chaque</p>\r\n\r\n<p>DUT LASH : beatrix.pernelle@unice.fr</p>\r\n\r\n<p>M&Eacute;DECINE : emmanuel.barranger@unice.fr</p>\r\n\r\n<h2>UCA Sport Club</h2>\r\n\r\n<p>C&rsquo;est l&rsquo;association sportive d&rsquo;UCA. Elle permet aux &eacute;tudiants athl&egrave;tes de faire briller les couleurs de leur universit&eacute; et de leur club en championnat universitaire de France, d&rsquo;Europe et du Monde.</p>\r\n\r\n<h2>Les r&eacute;f&eacute;rents disciplines sportives</h2>\r\n\r\n<p>Ce sont les encadrants sportifs qui accompagnent, s&eacute;lectionnent et encadrent les athl&egrave;tes universitaires pour leur participation aux diff&eacute;rentes comp&eacute;titions.</p>\r\n\r\n<h2>La F&eacute;d&eacute;ration Fran&ccedil;aise de Sport Universitaire</h2>\r\n\r\n<p>Elle organise et promeut le sport de comp&eacute;tition pour l&rsquo;ensemble des &eacute;tudiants de l&rsquo;Enseignement Sup&eacute;rieur (Universit&eacute;s et Grandes &Eacute;coles). Elle dispose d&rsquo;une ligue du Sud, qui assure le relai local.</p>', 0),
    (9, 'accompagnement', 'ACCOMPAGNEMENT', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sit amet ante a ante auctor sagittis nec quis ex. Suspendisse venenatis aliquam porta. Maecenas vitae nisi eu leo fringilla vulputate. Phasellus nec vulputate nunc. Aenean nec nunc pellentesque, tincidunt neque at, commodo libero. Morbi iaculis dui ipsum, mattis accumsan libero imperdiet in. Sed fermentum, odio sed semper pretium, nunc erat sagittis ante, vel fringilla purus orci ac nisl. Donec volutpat porta magna sed pellentesque. Mauris sed eros vel tortor iaculis molestie non ut justo. Aenean eros lorem, iaculis id mauris feugiat, posuere efficitur neque. Fusce tristique tellus ac neque vestibulum, id viverra odio tempus. Sed scelerisque augue aliquam, fringilla quam sed, convallis arcu. Ut ipsum augue, interdum in velit a, egestas euismod est. Suspendisse potenti. Vivamus at efficitur odio.</p>', 0),
    (10, 'procedure', 'PROCÉDURE', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sit amet ante a ante auctor sagittis nec quis ex. Suspendisse venenatis aliquam porta. Maecenas vitae nisi eu leo fringilla vulputate. Phasellus nec vulputate nunc. Aenean nec nunc pellentesque, tincidunt neque at, commodo libero. Morbi iaculis dui ipsum, mattis accumsan libero imperdiet in. Sed fermentum, odio sed semper pretium, nunc erat sagittis ante, vel fringilla purus orci ac nisl. Donec volutpat porta magna sed pellentesque. Mauris sed eros vel tortor iaculis molestie non ut justo. Aenean eros lorem, iaculis id mauris feugiat, posuere efficitur neque. Fusce tristique tellus ac neque vestibulum, id viverra odio tempus. Sed scelerisque augue aliquam, fringilla quam sed, convallis arcu. Ut ipsum augue, interdum in velit a, egestas euismod est. Suspendisse potenti. Vivamus at efficitur odio.</p>', 0),
    (11, 'inscriptions et tarifs', 'INSCRIPTION ET TARIFS', '<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\" style=\"width:800px\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"text-align:center\">\r\n			<p>Vous &ecirc;tes &eacute;tudiant ?</p>\r\n\r\n			<p>L&#39;inscription s&#39;effectue au moment de votre inscription p&eacute;dagogique ou bien aupr&egrave;s de la scolarit&eacute; de votre Campus.</p>\r\n\r\n			<p>Pour les U.E.L. (Unit&eacute; d&rsquo;Enseignement Libre) : s&rsquo;inscrire directement sur l&rsquo;E.N.T.</p>\r\n			</td>\r\n			<td style=\"text-align:center\">\r\n			<p>Vous &ecirc;tes membre du personnel ?</p>\r\n\r\n			<p>Pr&eacute;sentez-vous aux Bureaux des Sports avec une photo d&rsquo;identit&eacute;, un certificat m&eacute;dical &laquo; apte &agrave; la pratique du sport &raquo; et un ch&egrave;que &agrave; l&rsquo;ordre de l&rsquo;Agent Comptable de l&rsquo;U.N.S de 60&euro; afin de r&eacute;cup&eacute;rer sa carte UCA Sport.</p>\r\n\r\n			<p>Attention : le personnel n&rsquo;a pas acc&egrave;s aux cours d&rsquo;U.E.L.</p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>Cotisations sportives (taux de TVA applicable en vigueur sur l&#39;ann&eacute;e 2018)</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<table align=\"center\" border=\"1\" cellpadding=\"1\" cellspacing=\"1\" style=\"width:800px\">\r\n	<thead>\r\n		<tr>\r\n			<th scope=\"col\">Profil</th>\r\n			<th scope=\"col\">Profil</th>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td>Etudiant</td>\r\n			<td>CVEC acquitt&eacute;e &agrave; l&#39;inscription ou boursier</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Personnel</td>\r\n			<td>60 &euro;</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Partenaire</td>\r\n			<td>105 &euro;</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Membre d&#39;honneur, retrait&eacute; et alumni</td>\r\n			<td>150 &euro;</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>&nbsp;</p>', 0),
    (12, 'amenagement etude', 'AMENAGEMENT D\'ETUDE', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sit amet ante a ante auctor sagittis nec quis ex. Suspendisse venenatis aliquam porta. Maecenas vitae nisi eu leo fringilla vulputate. Phasellus nec vulputate nunc. Aenean nec nunc pellentesque, tincidunt neque at, commodo libero. Morbi iaculis dui ipsum, mattis accumsan libero imperdiet in. Sed fermentum, odio sed semper pretium, nunc erat sagittis ante, vel fringilla purus orci ac nisl. Donec volutpat porta magna sed pellentesque. Mauris sed eros vel tortor iaculis molestie non ut justo. Aenean eros lorem, iaculis id mauris feugiat, posuere efficitur neque. Fusce tristique tellus ac neque vestibulum, id viverra odio tempus. Sed scelerisque augue aliquam, fringilla quam sed, convallis arcu. Ut ipsum augue, interdum in velit a, egestas euismod est. Suspendisse potenti. Vivamus at efficitur odio.</p>', 0),
    (13, 'carte bonus sport', 'CARTE BONUS SPORT', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sit amet ante a ante auctor sagittis nec quis ex. Suspendisse venenatis aliquam porta. Maecenas vitae nisi eu leo fringilla vulputate. Phasellus nec vulputate nunc. Aenean nec nunc pellentesque, tincidunt neque at, commodo libero. Morbi iaculis dui ipsum, mattis accumsan libero imperdiet in. Sed fermentum, odio sed semper pretium, nunc erat sagittis ante, vel fringilla purus orci ac nisl. Donec volutpat porta magna sed pellentesque. Mauris sed eros vel tortor iaculis molestie non ut justo. Aenean eros lorem, iaculis id mauris feugiat, posuere efficitur neque. Fusce tristique tellus ac neque vestibulum, id viverra odio tempus. Sed scelerisque augue aliquam, fringilla quam sed, convallis arcu. Ut ipsum augue, interdum in velit a, egestas euismod est. Suspendisse potenti. Vivamus at efficitur odio.</p>', 0);

/* Image de fond */
INSERT INTO `image_fond` (`id`, `emplacement`, `titre`, `image`, `updated_at`) VALUES
(1, 'Accueil - Prochainement', 'Prochainement', '5d259e9b9c51e774085771.png', '2019-06-25 08:27:45'),
(2, 'Accueil - Activités', 'Activités sportives', '5d259ec83fca6244217124.png', '2019-06-25 08:27:57'),
(3, 'Accueil - Inscription', 'Inscription', '5d259ed305af6206985456.png', '2019-06-25 08:28:08'),
(8, 'Activités', 'Activités', '5d1a2359cb0b9464758516.jpg', '2019-07-01 15:14:33'),
(9, 'Installations', 'Installations', '5d273d02931a0336528586.jpg', '2019-06-25 08:34:07'),
(10, 'Evènements', 'Evènements', '5d288d38a0916209629458.jpg', '2019-06-25 08:34:18'),
(11, 'Sport de haut niveau', 'Sport de haut niveau', '5d288e3c72215818028236.jpg', '2019-06-25 08:34:27'),
(12, 'Infos pratiques', 'Infos pratiques', '5d288e481aba5289234306.jpg', '2019-06-25 08:34:35');


/* TRADUCTION */
INSERT INTO `ext_translations` (`id`, `locale`, `object_class`, `field`, `foreign_key`, `content`) VALUES
(1, 'en', 'UcaBundle\\Entity\\ClasseActivite', 'libelle', '1', 'Indoor sports'),
(2, 'en', 'UcaBundle\\Entity\\ClasseActivite', 'libelle', '2', 'Outdoor sport'),
(3, 'en', 'UcaBundle\\Entity\\ClasseActivite', 'libelle', '3', 'Raquet sport '),
(6, 'en', 'UcaBundle\\Entity\\ClasseActivite', 'libelle', '5', 'Water sports'),
(7, 'en', 'UcaBundle\\Entity\\ClasseActivite', 'description', '5', 'Swimming...'),
(8, 'en', 'UcaBundle\\Entity\\TypeActivite', 'libelle', '1', 'Sport En'),
(10, 'en', 'UcaBundle\\Entity\\ProfilUtilisateur', 'libelle', '3', 'Retiree'),
(11, 'en', 'UcaBundle\\Entity\\ProfilUtilisateur', 'libelle', '4', 'Student'),
(12, 'en', 'UcaBundle\\Entity\\ProfilUtilisateur', 'libelle', '6', 'Alumnis En'),
(13, 'en', 'UcaBundle\\Entity\\ProfilUtilisateur', 'libelle', '7', 'Partners'),
(14, 'en', 'UcaBundle\\Entity\\ProfilUtilisateur', 'libelle', '8', 'Staff Members'),
(15, 'en', 'UcaBundle\\Entity\\Jour', 'libelle', '1', 'Monday'),
(16, 'en', 'UcaBundle\\Entity\\Jour', 'libelle', '2', 'Tuesday'),
(17, 'en', 'UcaBundle\\Entity\\Jour', 'libelle', '3', 'Wednesday'),
(18, 'en', 'UcaBundle\\Entity\\Jour', 'libelle', '4', 'Thurday'),
(19, 'en', 'UcaBundle\\Entity\\Jour', 'libelle', '5', 'Friday'),
(20, 'en', 'UcaBundle\\Entity\\Jour', 'libelle', '6', 'Saturday'),
(21, 'en', 'UcaBundle\\Entity\\Jour', 'libelle', '7', 'Sunday'),
(40, 'en', 'UcaBundle\\Entity\\Activite', 'description', '1', 'Basketball Practice'),
(41, 'en', 'UcaBundle\\Entity\\Activite', 'libelle', '1', 'Basketball'),
(42, 'en', 'UcaBundle\\Entity\\Activite', 'libelle', '2', 'Swimming'),
(43, 'en', 'UcaBundle\\Entity\\Activite', 'description', '2', 'Swimming practice'),
(44, 'en', 'UcaBundle\\Entity\\Activite', 'libelle', '3', 'Bodybuilding'),
(45, 'en', 'UcaBundle\\Entity\\Activite', 'description', '3', 'Bodybuilding practice'),
(46, 'en', 'UcaBundle\\Entity\\Activite', 'libelle', '4', 'Ski'),
(47, 'en', 'UcaBundle\\Entity\\Activite', 'description', '4', 'Ski practice'),
(48, 'en', 'UcaBundle\\Entity\\Activite', 'libelle', '5', 'Tennis'),
(49, 'en', 'UcaBundle\\Entity\\Activite', 'description', '5', 'Tennis practice'),
(50, 'en', 'UcaBundle\\Entity\\ClasseActivite', 'libelle', '10', 'Team sport'),
(51, 'en', 'UcaBundle\\Entity\\Texte', 'texte', '1', '<p>Descriptive text for practical informations (for Desktop version)</p>'),
(52, 'en', 'UcaBundle\\Entity\\Texte', 'texteMobile', '1', '<p>Descriptive text</p>'),
(53, 'en', 'UcaBundle\\Entity\\ComportementAutorisation', 'libelle', '1', 'Membership fee'),
(54, 'en', 'UcaBundle\\Entity\\ComportementAutorisation', 'libelle', '2', 'Proof to provide'),
(55, 'en', 'UcaBundle\\Entity\\Texte', 'titre', '2', 'High level sport'),
(56, 'en', 'UcaBundle\\Entity\\Texte', 'texte', '2', '<p>Description for&nbsp;High level sport</p>'),
(57, 'en', 'UcaBundle\\Entity\\ComportementAutorisation', 'libelle', '3', 'Check box'),
(58, 'en', 'UcaBundle\\Entity\\Texte', 'titre', '1', 'Pratical informations'),
(59, 'en', 'UcaBundle\\Entity\\ComportementAutorisation', 'libelle', '4', 'Card purchase'),
(60, 'en', 'UcaBundle\\Entity\\FormatActivite', 'libelle', '1', 'Monday Niche Basketball Course'),
(61, 'en', 'UcaBundle\\Entity\\FormatActivite', 'libelle', '22', 'Reservation of the court n ° 1'),
(62, 'en', 'UcaBundle\\Entity\\FormatActivite', 'description', '22', 'Reservation of the court n ° 1'),
(63, 'en', 'UcaBundle\\Entity\\FormatActivite', 'libelle', '11', 'Purchase tennis card'),
(64, 'en', 'UcaBundle\\Entity\\FormatActivite', 'libelle', '21', 'Beginner course Monday 9am'),
(65, 'en', 'UcaBundle\\Entity\\FormatActivite', 'description', '1', 'Course for beginner and intermediate levels'),
(66, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '1', 'Coming soon'),
(67, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '3', 'Inscription'),
(68, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '9', 'Installations'),
(69, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'libelle', '14', 'Tennis card'),
(70, 'en', 'UcaBundle\\Entity\\FormatActivite', 'libelle', '23', 'Double tournament'),
(71, 'en', 'UcaBundle\\Entity\\FormatActivite', 'description', '11', 'Provides access to the booking of tennis lessons'),
(72, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '2', 'Sporting activities'),
(73, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '12', 'Practical information'),
(74, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '11', 'High-level sport'),
(75, 'en', 'UcaBundle\\Entity\\NiveauSportif', 'libelle', '1', 'Beginner'),
(76, 'en', 'UcaBundle\\Entity\\Ressource', 'libelle', '27', 'Tennis court 1'),
(77, 'en', 'UcaBundle\\Entity\\NiveauSportif', 'libelle', '3', 'Expert'),
(78, 'en', 'UcaBundle\\Entity\\Ressource', 'description', '28', 'Clay tennis court 2'),
(79, 'en', 'UcaBundle\\Entity\\Ressource', 'description', '27', 'Clay tennis court 1'),
(80, 'en', 'UcaBundle\\Entity\\Ressource', 'libelle', '29', 'Tennis rackets'),
(81, 'en', 'UcaBundle\\Entity\\Ressource', 'libelle', '28', 'Tennis court 2'),
(82, 'en', 'UcaBundle\\Entity\\Tarif', 'libelle', '15', 'Sports contribution'),
(83, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '8', 'Activities'),
(84, 'en', 'UcaBundle\\Entity\\Tarif', 'libelle', '33', 'Tennis - Framed Course'),
(85, 'en', 'UcaBundle\\Entity\\Tarif', 'libelle', '25', 'Tennis card'),
(86, 'en', 'UcaBundle\\Entity\\Tarif', 'libelle', '34', 'Tennis - Morning slots'),
(87, 'en', 'UcaBundle\\Entity\\Tarif', 'libelle', '35', 'Tennis - Evening Slots'),
(88, 'en', 'UcaBundle\\Entity\\Tarif', 'libelle', '36', 'Free'),
(89, 'en', 'UcaBundle\\Entity\\Texte', 'texteMobile', '2', NULL),
(90, 'en', 'UcaBundle\\Entity\\Tarif', 'libelle', '37', 'Tennis racket rental'),
(91, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'libelle', '2', 'Sports contribution'),
(92, 'en', 'UcaBundle\\Entity\\FormatActivite', 'description', '23', 'Tennis tournament of 25/12/2019'),
(93, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'libelle', '3', 'Medical certificate'),
(94, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'libelle', '4', 'Diving authorization'),
(95, 'en', 'UcaBundle\\Entity\\ImageFond', 'titre', '10', 'Events'),
(96, 'en', 'UcaBundle\\Entity\\NiveauSportif', 'libelle', '2', 'Intermediary'),
(97, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'informationsComplementaires', '3', 'I certify that I am in possession of a medical certificate to participate in this activity'),
(98, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'libelle', '5', 'Climbing authorization'),
(99, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'libelle', '12', 'Swimming Pool Card'),
(100, 'en', 'UcaBundle\\Entity\\TypeAutorisation', 'libelle', '13', 'Card Bodybuilding'),
(101, 'en', 'UcaBundle\\Entity\\FormatActivite', 'description', '21', 'Beginner course Monday 9am'),
(102, 'en', 'UcaBundle\\Entity\\TypeActivite', 'libelle', '7', 'Culture EN');

INSERT INTO `actualite` (`id`, `ordre`, `titre`, `texte`, `image`, `updated_at`) VALUES
(1, 0, 'Tournoi Tennis 2019', '<p>Retrouvez-nous lors de notre prochain Tournoi de tennis en d&eacute;cembre 2019</p>', '5d25a50a7b9ad740379664.jpg', '2019-07-10 08:42:50'),
(2, 1, 'Tournoi de Basket Inter-campus', '<p>Nouveaut&eacute; 2020</p>\r\n\r\n<p>Tournoi de basket intercampus du&nbsp; 3 au 5 avril 2020</p>\r\n\r\n<p>Ouverture des inscriptions - Novembre 2019</p>', '5d25a647e9b65506010859.jpg', '2019-07-10 08:48:07'),
(3, 2, 'Natation 2019-2020', '<p>Les inscriptions pour les cours de natation 2019/2020 sont ouvertes</p>', '5d25a71261b9d176115450.jpg', '2019-07-10 08:51:30');

INSERT INTO `creneau` (`id`, `lieu_id`, `format_activite_id`, `tarif_id`, `capacite`) VALUES
(1, NULL, 21, 36, 30),
(2, NULL, 21, 33, 30),
(3, NULL, 21, 36, 30),
(4, NULL, 21, 36, 30),
(5, NULL, 21, 36, 30),
(6, NULL, 21, 36, 30),
(7, NULL, 21, 36, 30),
(8, NULL, 21, 36, 30),
(9, NULL, 21, 36, 30),
(10, NULL, 21, 35, 30),
(11, NULL, 21, 33, 30),
(12, NULL, 21, 35, 30),
(13, NULL, 21, 34, 30);

INSERT INTO `creneau_profil_utilisateur` (`creneau_id`, `profil_utilisateur_id`) VALUES
(1, 3),
(1, 4),
(1, 6),
(1, 7),
(1, 8),
(2, 3),
(2, 4),
(2, 6),
(2, 7),
(2, 8),
(3, 3),
(3, 4),
(3, 6),
(3, 7),
(3, 8),
(4, 3),
(4, 4),
(4, 6),
(4, 7),
(4, 8),
(5, 3),
(5, 4),
(5, 6),
(5, 7),
(5, 8),
(6, 3),
(6, 4),
(6, 6),
(6, 7),
(6, 8),
(7, 3),
(7, 4),
(7, 6),
(7, 7),
(7, 8),
(8, 3),
(8, 4),
(8, 6),
(8, 7),
(8, 8),
(9, 3),
(9, 4),
(9, 6),
(9, 7),
(9, 8),
(10, 3),
(10, 4),
(10, 6),
(10, 7),
(10, 8),
(11, 3),
(11, 4),
(11, 6),
(11, 7),
(11, 8),
(12, 3),
(13, 3),
(13, 4),
(13, 6),
(13, 7),
(13, 8);

INSERT INTO `creneau_utilisateur` (`creneau_id`, `utilisateur_id`) VALUES
(1, 8), 
(2, 8),
(3, 8),
(4, 8),
(5, 8),
(6, 8);

INSERT INTO `dhtmlx_date` (`id`, `serie_id`, `reservabilite_id`, `creneau_id`, `date_debut`, `date_fin`, `format`, `dependance_serie`, `description`, `recurrence`, `date_fin_serie`) VALUES
(1, NULL, NULL, 1, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(2, 1, NULL, NULL, '2019-07-08 08:00:00', '2019-07-08 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(3, 1, NULL, NULL, '2019-07-15 08:00:00', '2019-07-15 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(4, 1, NULL, NULL, '2019-07-22 08:00:00', '2019-07-22 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(5, 1, NULL, NULL, '2019-07-29 08:00:00', '2019-07-29 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(6, 1, NULL, NULL, '2019-08-05 08:00:00', '2019-08-05 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(7, 1, NULL, NULL, '2019-08-12 08:00:00', '2019-08-12 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(8, 1, NULL, NULL, '2019-08-19 08:00:00', '2019-08-19 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(9, 1, NULL, NULL, '2019-08-26 08:00:00', '2019-08-26 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(10, 1, NULL, NULL, '2019-09-02 08:00:00', '2019-09-02 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(11, 1, NULL, NULL, '2019-09-09 08:00:00', '2019-09-09 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(12, 1, NULL, NULL, '2019-09-16 08:00:00', '2019-09-16 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(13, 1, NULL, NULL, '2019-09-23 08:00:00', '2019-09-23 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(14, 1, NULL, NULL, '2019-09-30 08:00:00', '2019-09-30 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(15, 1, NULL, NULL, '2019-10-07 08:00:00', '2019-10-07 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(16, 1, NULL, NULL, '2019-10-14 08:00:00', '2019-10-14 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(17, 1, NULL, NULL, '2019-10-21 08:00:00', '2019-10-21 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(18, 1, NULL, NULL, '2019-10-28 08:00:00', '2019-10-28 09:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 9h', NULL, NULL),
(19, NULL, NULL, 2, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(20, 19, NULL, NULL, '2019-07-08 10:00:00', '2019-07-08 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(21, 19, NULL, NULL, '2019-07-15 10:00:00', '2019-07-15 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(22, 19, NULL, NULL, '2019-07-22 10:00:00', '2019-07-22 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(23, 19, NULL, NULL, '2019-07-29 10:00:00', '2019-07-29 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(24, 19, NULL, NULL, '2019-08-05 10:00:00', '2019-08-05 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(25, 19, NULL, NULL, '2019-08-12 10:00:00', '2019-08-12 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(26, 19, NULL, NULL, '2019-08-19 10:00:00', '2019-08-19 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(27, 19, NULL, NULL, '2019-08-26 10:00:00', '2019-08-26 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(28, 19, NULL, NULL, '2019-09-02 10:00:00', '2019-09-02 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(29, 19, NULL, NULL, '2019-09-09 10:00:00', '2019-09-09 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(30, 19, NULL, NULL, '2019-09-16 10:00:00', '2019-09-16 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(31, 19, NULL, NULL, '2019-09-23 10:00:00', '2019-09-23 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(32, 19, NULL, NULL, '2019-09-30 10:00:00', '2019-09-30 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(33, 19, NULL, NULL, '2019-10-07 10:00:00', '2019-10-07 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(34, 19, NULL, NULL, '2019-10-14 10:00:00', '2019-10-14 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(35, 19, NULL, NULL, '2019-10-21 10:00:00', '2019-10-21 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(36, 19, NULL, NULL, '2019-10-28 10:00:00', '2019-10-28 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 10h', NULL, NULL),
(37, NULL, NULL, 3, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(38, 37, NULL, NULL, '2019-07-09 12:00:00', '2019-07-09 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(39, 37, NULL, NULL, '2019-07-16 12:00:00', '2019-07-16 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(40, 37, NULL, NULL, '2019-07-23 12:00:00', '2019-07-23 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(41, 37, NULL, NULL, '2019-07-30 12:00:00', '2019-07-30 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(42, 37, NULL, NULL, '2019-08-06 12:00:00', '2019-08-06 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(43, 37, NULL, NULL, '2019-08-13 12:00:00', '2019-08-13 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(44, 37, NULL, NULL, '2019-08-20 12:00:00', '2019-08-20 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(45, 37, NULL, NULL, '2019-08-27 12:00:00', '2019-08-27 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(46, 37, NULL, NULL, '2019-09-03 12:00:00', '2019-09-03 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(47, 37, NULL, NULL, '2019-09-10 12:00:00', '2019-09-10 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(48, 37, NULL, NULL, '2019-09-17 12:00:00', '2019-09-17 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(49, 37, NULL, NULL, '2019-09-24 12:00:00', '2019-09-24 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(50, 37, NULL, NULL, '2019-10-01 12:00:00', '2019-10-01 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(51, 37, NULL, NULL, '2019-10-08 12:00:00', '2019-10-08 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(52, 37, NULL, NULL, '2019-10-15 12:00:00', '2019-10-15 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(53, 37, NULL, NULL, '2019-10-22 12:00:00', '2019-10-22 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(54, 37, NULL, NULL, '2019-10-29 12:00:00', '2019-10-29 13:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 12h', NULL, NULL),
(55, NULL, NULL, 4, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(56, 55, NULL, NULL, '2019-07-09 14:00:00', '2019-07-09 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(57, 55, NULL, NULL, '2019-07-16 14:00:00', '2019-07-16 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(58, 55, NULL, NULL, '2019-07-23 14:00:00', '2019-07-23 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(59, 55, NULL, NULL, '2019-07-30 14:00:00', '2019-07-30 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(60, 55, NULL, NULL, '2019-08-06 14:00:00', '2019-08-06 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(61, 55, NULL, NULL, '2019-08-13 14:00:00', '2019-08-13 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(62, 55, NULL, NULL, '2019-08-20 14:00:00', '2019-08-20 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(63, 55, NULL, NULL, '2019-08-27 14:00:00', '2019-08-27 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(64, 55, NULL, NULL, '2019-09-03 14:00:00', '2019-09-03 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(65, 55, NULL, NULL, '2019-09-10 14:00:00', '2019-09-10 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(66, 55, NULL, NULL, '2019-09-17 14:00:00', '2019-09-17 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(67, 55, NULL, NULL, '2019-09-24 14:00:00', '2019-09-24 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(68, 55, NULL, NULL, '2019-10-01 14:00:00', '2019-10-01 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(69, 55, NULL, NULL, '2019-10-08 14:00:00', '2019-10-08 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(70, 55, NULL, NULL, '2019-10-15 14:00:00', '2019-10-15 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(71, 55, NULL, NULL, '2019-10-22 14:00:00', '2019-10-22 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(72, 55, NULL, NULL, '2019-10-29 14:00:00', '2019-10-29 15:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Mardi 14h', NULL, NULL),
(73, NULL, NULL, 5, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(74, 73, NULL, NULL, '2019-07-10 10:30:00', '2019-07-10 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(75, 73, NULL, NULL, '2019-07-17 10:30:00', '2019-07-17 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(76, 73, NULL, NULL, '2019-07-24 10:30:00', '2019-07-24 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(77, 73, NULL, NULL, '2019-07-31 10:30:00', '2019-07-31 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(78, 73, NULL, NULL, '2019-08-07 10:30:00', '2019-08-07 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(79, 73, NULL, NULL, '2019-08-14 10:30:00', '2019-08-14 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(80, 73, NULL, NULL, '2019-08-21 10:30:00', '2019-08-21 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(81, 73, NULL, NULL, '2019-08-28 10:30:00', '2019-08-28 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(82, 73, NULL, NULL, '2019-09-04 10:30:00', '2019-09-04 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(83, 73, NULL, NULL, '2019-09-11 10:30:00', '2019-09-11 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(84, 73, NULL, NULL, '2019-09-18 10:30:00', '2019-09-18 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(85, 73, NULL, NULL, '2019-09-25 10:30:00', '2019-09-25 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(86, 73, NULL, NULL, '2019-10-02 10:30:00', '2019-10-02 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(87, 73, NULL, NULL, '2019-10-09 10:30:00', '2019-10-09 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(88, 73, NULL, NULL, '2019-10-16 10:30:00', '2019-10-16 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(89, 73, NULL, NULL, '2019-10-23 10:30:00', '2019-10-23 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(90, 73, NULL, NULL, '2019-10-30 10:30:00', '2019-10-30 11:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Mercredi 0h30', NULL, NULL),
(91, NULL, NULL, 6, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(92, 91, NULL, NULL, '2019-07-11 09:30:00', '2019-07-11 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(93, 91, NULL, NULL, '2019-07-18 09:30:00', '2019-07-18 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(94, 91, NULL, NULL, '2019-07-25 09:30:00', '2019-07-25 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(95, 91, NULL, NULL, '2019-08-01 09:30:00', '2019-08-01 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(96, 91, NULL, NULL, '2019-08-08 09:30:00', '2019-08-08 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(97, 91, NULL, NULL, '2019-08-15 09:30:00', '2019-08-15 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(98, 91, NULL, NULL, '2019-08-22 09:30:00', '2019-08-22 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(99, 91, NULL, NULL, '2019-08-29 09:30:00', '2019-08-29 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(100, 91, NULL, NULL, '2019-09-05 09:30:00', '2019-09-05 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(101, 91, NULL, NULL, '2019-09-12 09:30:00', '2019-09-12 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(102, 91, NULL, NULL, '2019-09-19 09:30:00', '2019-09-19 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(103, 91, NULL, NULL, '2019-09-26 09:30:00', '2019-09-26 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(104, 91, NULL, NULL, '2019-10-03 09:30:00', '2019-10-03 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(105, 91, NULL, NULL, '2019-10-10 09:30:00', '2019-10-10 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(106, 91, NULL, NULL, '2019-10-17 09:30:00', '2019-10-17 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(107, 91, NULL, NULL, '2019-10-24 09:30:00', '2019-10-24 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(108, 91, NULL, NULL, '2019-10-31 09:30:00', '2019-10-31 10:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Jeudi 9h30', NULL, NULL),
(109, NULL, NULL, 7, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(110, 109, NULL, NULL, '2019-07-13 10:00:00', '2019-07-13 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(111, 109, NULL, NULL, '2019-07-20 10:00:00', '2019-07-20 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(112, 109, NULL, NULL, '2019-07-27 10:00:00', '2019-07-27 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(113, 109, NULL, NULL, '2019-08-03 10:00:00', '2019-08-03 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(114, 109, NULL, NULL, '2019-08-10 10:00:00', '2019-08-10 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(115, 109, NULL, NULL, '2019-08-17 10:00:00', '2019-08-17 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(116, 109, NULL, NULL, '2019-08-24 10:00:00', '2019-08-24 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(117, 109, NULL, NULL, '2019-08-31 10:00:00', '2019-08-31 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(118, 109, NULL, NULL, '2019-09-07 10:00:00', '2019-09-07 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(119, 109, NULL, NULL, '2019-09-14 10:00:00', '2019-09-14 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(120, 109, NULL, NULL, '2019-09-21 10:00:00', '2019-09-21 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(121, 109, NULL, NULL, '2019-09-28 10:00:00', '2019-09-28 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(122, 109, NULL, NULL, '2019-10-05 10:00:00', '2019-10-05 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(123, 109, NULL, NULL, '2019-10-12 10:00:00', '2019-10-12 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(124, 109, NULL, NULL, '2019-10-19 10:00:00', '2019-10-19 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(125, 109, NULL, NULL, '2019-10-26 10:00:00', '2019-10-26 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(126, 109, NULL, NULL, '2019-11-02 10:00:00', '2019-11-02 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Samedi 10h', NULL, NULL),
(127, NULL, NULL, 8, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(128, 127, NULL, NULL, '2019-07-12 08:00:00', '2019-07-12 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(129, 127, NULL, NULL, '2019-07-19 08:00:00', '2019-07-19 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(130, 127, NULL, NULL, '2019-07-26 08:00:00', '2019-07-26 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(131, 127, NULL, NULL, '2019-08-02 08:00:00', '2019-08-02 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(132, 127, NULL, NULL, '2019-08-09 08:00:00', '2019-08-09 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(133, 127, NULL, NULL, '2019-08-16 08:00:00', '2019-08-16 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(134, 127, NULL, NULL, '2019-08-23 08:00:00', '2019-08-23 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(135, 127, NULL, NULL, '2019-08-30 08:00:00', '2019-08-30 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(136, 127, NULL, NULL, '2019-09-06 08:00:00', '2019-09-06 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(137, 127, NULL, NULL, '2019-09-13 08:00:00', '2019-09-13 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(138, 127, NULL, NULL, '2019-09-20 08:00:00', '2019-09-20 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(139, 127, NULL, NULL, '2019-09-27 08:00:00', '2019-09-27 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(140, 127, NULL, NULL, '2019-10-04 08:00:00', '2019-10-04 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(141, 127, NULL, NULL, '2019-10-11 08:00:00', '2019-10-11 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(142, 127, NULL, NULL, '2019-10-18 08:00:00', '2019-10-18 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(143, 127, NULL, NULL, '2019-10-25 08:00:00', '2019-10-25 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(144, 127, NULL, NULL, '2019-11-01 08:00:00', '2019-11-01 10:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 8h', NULL, NULL),
(145, NULL, NULL, 9, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(146, 145, NULL, NULL, '2019-07-08 16:30:00', '2019-07-08 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(147, 145, NULL, NULL, '2019-07-15 16:30:00', '2019-07-15 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(148, 145, NULL, NULL, '2019-07-22 16:30:00', '2019-07-22 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(149, 145, NULL, NULL, '2019-07-29 16:30:00', '2019-07-29 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(150, 145, NULL, NULL, '2019-08-05 16:30:00', '2019-08-05 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(151, 145, NULL, NULL, '2019-08-12 16:30:00', '2019-08-12 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(152, 145, NULL, NULL, '2019-08-19 16:30:00', '2019-08-19 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(153, 145, NULL, NULL, '2019-08-26 16:30:00', '2019-08-26 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(154, 145, NULL, NULL, '2019-09-02 16:30:00', '2019-09-02 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(155, 145, NULL, NULL, '2019-09-09 16:30:00', '2019-09-09 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(156, 145, NULL, NULL, '2019-09-16 16:30:00', '2019-09-16 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(157, 145, NULL, NULL, '2019-09-23 16:30:00', '2019-09-23 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(158, 145, NULL, NULL, '2019-09-30 16:30:00', '2019-09-30 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(159, 145, NULL, NULL, '2019-10-07 16:30:00', '2019-10-07 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(160, 145, NULL, NULL, '2019-10-14 16:30:00', '2019-10-14 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(161, 145, NULL, NULL, '2019-10-21 16:30:00', '2019-10-21 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(162, 145, NULL, NULL, '2019-10-28 16:30:00', '2019-10-28 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 16h30', NULL, NULL),
(163, NULL, NULL, 10, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(164, 163, NULL, NULL, '2019-07-08 15:00:00', '2019-07-08 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(165, 163, NULL, NULL, '2019-07-15 15:00:00', '2019-07-15 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(166, 163, NULL, NULL, '2019-07-22 15:00:00', '2019-07-22 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(167, 163, NULL, NULL, '2019-07-29 15:00:00', '2019-07-29 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(168, 163, NULL, NULL, '2019-08-05 15:00:00', '2019-08-05 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(169, 163, NULL, NULL, '2019-08-12 15:00:00', '2019-08-12 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(170, 163, NULL, NULL, '2019-08-19 15:00:00', '2019-08-19 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(171, 163, NULL, NULL, '2019-08-26 15:00:00', '2019-08-26 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(172, 163, NULL, NULL, '2019-09-02 15:00:00', '2019-09-02 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(173, 163, NULL, NULL, '2019-09-09 15:00:00', '2019-09-09 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(174, 163, NULL, NULL, '2019-09-16 15:00:00', '2019-09-16 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(175, 163, NULL, NULL, '2019-09-23 15:00:00', '2019-09-23 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(176, 163, NULL, NULL, '2019-09-30 15:00:00', '2019-09-30 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(177, 163, NULL, NULL, '2019-10-07 15:00:00', '2019-10-07 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(178, 163, NULL, NULL, '2019-10-14 15:00:00', '2019-10-14 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(179, 163, NULL, NULL, '2019-10-21 15:00:00', '2019-10-21 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(180, 163, NULL, NULL, '2019-10-28 15:00:00', '2019-10-28 16:30:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15', NULL, NULL),
(181, NULL, NULL, 11, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(182, 181, NULL, NULL, '2019-07-08 13:00:00', '2019-07-08 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(183, 181, NULL, NULL, '2019-07-15 13:00:00', '2019-07-15 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(184, 181, NULL, NULL, '2019-07-22 13:00:00', '2019-07-22 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(185, 181, NULL, NULL, '2019-07-29 13:00:00', '2019-07-29 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(186, 181, NULL, NULL, '2019-08-05 13:00:00', '2019-08-05 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(187, 181, NULL, NULL, '2019-08-12 13:00:00', '2019-08-12 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(188, 181, NULL, NULL, '2019-08-19 13:00:00', '2019-08-19 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(189, 181, NULL, NULL, '2019-08-26 13:00:00', '2019-08-26 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(190, 181, NULL, NULL, '2019-09-02 13:00:00', '2019-09-02 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(191, 181, NULL, NULL, '2019-09-09 13:00:00', '2019-09-09 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(192, 181, NULL, NULL, '2019-09-16 13:00:00', '2019-09-16 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(193, 181, NULL, NULL, '2019-09-23 13:00:00', '2019-09-23 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(194, 181, NULL, NULL, '2019-09-30 13:00:00', '2019-09-30 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(195, 181, NULL, NULL, '2019-10-07 13:00:00', '2019-10-07 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(196, 181, NULL, NULL, '2019-10-14 13:00:00', '2019-10-14 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(197, 181, NULL, NULL, '2019-10-21 13:00:00', '2019-10-21 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(198, 181, NULL, NULL, '2019-10-28 13:00:00', '2019-10-28 14:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 13h', NULL, NULL),
(199, NULL, NULL, 12, '2019-07-10 09:45:00', '2019-07-10 09:45:00', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(200, 199, NULL, NULL, '2019-07-08 15:00:00', '2019-07-08 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(201, 199, NULL, NULL, '2019-07-15 15:00:00', '2019-07-15 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(202, 199, NULL, NULL, '2019-07-22 15:00:00', '2019-07-22 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(203, 199, NULL, NULL, '2019-07-29 15:00:00', '2019-07-29 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(204, 199, NULL, NULL, '2019-08-05 15:00:00', '2019-08-05 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(205, 199, NULL, NULL, '2019-08-12 15:00:00', '2019-08-12 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(206, 199, NULL, NULL, '2019-08-19 15:00:00', '2019-08-19 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(207, 199, NULL, NULL, '2019-08-26 15:00:00', '2019-08-26 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(208, 199, NULL, NULL, '2019-09-02 15:00:00', '2019-09-02 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(209, 199, NULL, NULL, '2019-09-09 15:00:00', '2019-09-09 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(210, 199, NULL, NULL, '2019-09-16 15:00:00', '2019-09-16 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(211, 199, NULL, NULL, '2019-09-23 15:00:00', '2019-09-23 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(212, 199, NULL, NULL, '2019-09-30 15:00:00', '2019-09-30 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(213, 199, NULL, NULL, '2019-10-07 15:00:00', '2019-10-07 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(214, 199, NULL, NULL, '2019-10-14 15:00:00', '2019-10-14 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(215, 199, NULL, NULL, '2019-10-21 15:00:00', '2019-10-21 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(216, 199, NULL, NULL, '2019-10-28 15:00:00', '2019-10-28 18:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Lundi 15h (Retraités)', NULL, NULL),
(217, NULL, NULL, 13, '2019-07-10 09:50:52', '2019-07-10 09:50:52', 'DhtmlxSerie', 0, NULL, NULL, NULL),
(218, 217, NULL, NULL, '2019-07-12 09:00:00', '2019-07-12 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(219, 217, NULL, NULL, '2019-07-19 09:00:00', '2019-07-19 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(220, 217, NULL, NULL, '2019-07-26 09:00:00', '2019-07-26 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(221, 217, NULL, NULL, '2019-08-02 09:00:00', '2019-08-02 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(222, 217, NULL, NULL, '2019-08-09 09:00:00', '2019-08-09 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(223, 217, NULL, NULL, '2019-08-16 09:00:00', '2019-08-16 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(224, 217, NULL, NULL, '2019-08-23 09:00:00', '2019-08-23 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(225, 217, NULL, NULL, '2019-08-30 09:00:00', '2019-08-30 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(226, 217, NULL, NULL, '2019-09-06 09:00:00', '2019-09-06 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(227, 217, NULL, NULL, '2019-09-13 09:00:00', '2019-09-13 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(228, 217, NULL, NULL, '2019-09-20 09:00:00', '2019-09-20 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(229, 217, NULL, NULL, '2019-09-27 09:00:00', '2019-09-27 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(230, 217, NULL, NULL, '2019-10-04 09:00:00', '2019-10-04 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(231, 217, NULL, NULL, '2019-10-11 09:00:00', '2019-10-11 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(232, 217, NULL, NULL, '2019-10-18 09:00:00', '2019-10-18 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL),
(233, 217, NULL, NULL, '2019-10-25 09:00:00', '2019-10-25 11:00:00', 'DhtmlxEvenement', 1, 'Cours débutant Vendredi 9h', NULL, NULL);

INSERT INTO `inscription` (`id`, `format_activite_id`, `creneau_id`, `utilisateur_id`, `date`, `statut`) VALUES
(1, 1, 1, 6, '2019-07-11 00:00:00', 'e'),
(2, 22, NULL, 6, '2019-07-15 10:00:00', 'e'),
(3, 23, NULL, 6, '2019-10-07 00:00:00', 'f'),
(4, 35, NULL, 6, '2019-10-01 00:00:00', 's');