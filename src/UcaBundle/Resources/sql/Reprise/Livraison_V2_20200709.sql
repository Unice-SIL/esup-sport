INSERT INTO `image_fond` (`id`, `emplacement`, `titre`, `image`, `updated_at`) VALUES
(15, 'SHN - Candidater', 'Candidater', '', NULL),
(16, 'SHN - Highlights', 'Highlights', '', NULL),
(17, 'SHN - Representer', 'Représenter', '', NULL),
(18, 'SHN - Partenaires', 'Partenaires', '', NULL);



DELETE FROM `texte` WHERE `texte`.`id` = 7;
DELETE FROM `texte` WHERE `texte`.`id` = 8;
DELETE FROM `texte` WHERE `texte`.`id` = 9;
DELETE FROM `texte` WHERE `texte`.`id` = 10;
DELETE FROM `texte` WHERE `texte`.`id` = 15;
DELETE FROM `texte` WHERE `texte`.`id` = 16;
INSERT INTO `texte` (`id`, `emplacement`, `titre`, `texte`, `mobile`, `texte_mobile`) VALUES
(22, 'Partenaires - 01', 'Texte paramétrable - 01', NULL, 0, NULL),
(23, 'Partenaires - 02', 'Texte paramétrable - 02', NULL, 0, NULL),
(24, 'SHN - Accompagnement', 'SHN - Accompagnement', NULL, 0, NULL);