DROP TABLE IF EXISTS `email`;
CREATE TABLE IF NOT EXISTS `email` (
  `id` int NOT NULL AUTO_INCREMENT,
  `corps` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `email`
--

INSERT INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Votre commande n&deg;[[numeroCommande]]a &eacute;t&eacute; annul&eacute;e.</p></div>', 'Annulation de la commande', 'AnulationCommande'), 
(NULL, '<div>Bonjour,<p>Linscription portant l&#39;id&nbsp;[[id_inscription]] n&#39;a pas pu &ecirc;tre annul&eacute;e par le timeout.<br />Il semblerait que la commande &agrave; laquelle elle est associ&eacute;e soit valide et termin&eacute;e.</p></div>', 'TIMEOUT : erreur annulation inscription', 'ErreurAnnulationInscription'), 
(NULL, '<div>Bonjour,<p>La commande n&deg;[[numeroCommande]] n&#39;a pas pu &ecirc;tre termin&eacute;e.<br />Le montant PAYBOX pay&eacute; par l&#39;utilisateur est de&nbsp;[[montantPaybox]] &euro; alors que le montant totale de la commande est de [[montantTotal]]&euro;.</p></div>', 'Erreur retour paiement PAYBOX', 'ErreurMontantPaybox'), 
(NULL, '<div>Bonjour,<p>Le paiement de votre commande n&deg;[[numeroCommande]]a bien &eacute;t&eacute; enregistr&eacute;e. Vous pouvez acc&eacute;der &agrave; la facture de votre commande avec le lien suivant :&nbsp;[[lienFacture]]</p></div>', 'Validation de la commande', 'ValidationCommande'), 
(NULL, '<p>Votre compte est bloqu&eacute;</p>', 'Votre compte est bloqué', 'UtilisateurBloquerEmail'), 
(NULL, '<p>Votre compte est activ&eacute;</p><p>&nbsp;</p>', 'Votre compte est activé', 'UtilisateurDebloquerEmail'), 
(NULL, '<p>Bonjour,<br />&nbsp;</p><p>Vous avez une demande de contact de la part de :&nbsp;[[contact_from]]</p><p>Sujet :&nbsp;[[objet]]<br />Corps du message :<br />[[message]]</p>', '[[objet]]', 'ContactEmail'), 
(NULL, '<p>Bonjour,<br />&nbsp;</p><p>Le message suivant vous a &eacute;t&eacute; envoy&eacute; par l&#39;encadrant de l&#39;activit&eacute;:</p><p>[[message]]</p><p>&nbsp;</p>', '[[objet]]', 'ContactEmailing'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Vous avez &eacute;t&eacute; d&eacute;sinscrit &agrave; l&#39;activit&eacute; suivante:&nbsp;[[inscription]]<br />En cas de probl&egrave;me, merci de contacter le bureau des sports</p></div>', 'Désinscription', 'Desinscription'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Votre partenaire qui a initi&eacute; l&#39;inscription s&#39;est d&eacute;sinscrit de l&#39;activit&eacute; suivante : [[inscription]]<br />Votre commande a donc &eacute;t&eacute; annul&eacute;e.<br />En cas de probl&egrave;me, merci de contacter le bureau des sports.</p></div>', 'Désinscription partenaire', 'DesinscriptionPartenaire'), 
(NULL, '<p>Bonjour,<br />&nbsp;</p><p>Le [[date]], vous avez souhait&eacute; vous inscrire &agrave; l&#39;activit&eacute; suivante: [[inscription]]<br />Cette inscription n&eacute;cessite la validation d&#39;un encadrant :&nbsp;[[listeEncadrants]]</p>', 'Inscription', 'InscriptionAvecValidation'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Le [[date]],&nbsp;[[prenom]]&nbsp;[[nom]]&nbsp;([[mail]]) a souhait&eacute; s&#39;inscrire &agrave; l&#39;activit&eacute; suivante: [[inscription]]<br />Cette inscription n&eacute;cessite votre validation en tant [[statut]]</p><p>Pour valider cette inscription, vous pouvez vous rendre &agrave; l&#39;adresse suivante : [[lienInscription]]</p></div>', 'Demande d\'inscription', 'InscriptionDemandeValidation'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>L&#39;utilisateur [[prenom]]&nbsp;[[nom]]vous &agrave; ajout&eacute; en tant que partenaire pour un entra&icirc;nement !<br />Vous devez confirmer votre pr&eacute;sence au cr&eacute;neau de&nbsp;[[formatActivite]] de[[dateDebut]]&agrave;&nbsp;[[dateFin]] au [[etablissement]]-[[ressource]] le [[evenement]]<br /><br />Connectez-vous &agrave; votre compte UCA Sport, et cliquez sur le lien suivant : [[lienInscription]] pour vous permettre d&rsquo;ajouter l&rsquo;invitation &agrave; jouer &agrave; votre panier.</p><p>Si vous n&rsquo;avez pas encore de compte UCA Sport.<br />Cr&eacute;ez d&egrave;s &agrave; pr&eacute;sent votre compte <a href=\"{{ app.request != null ? app.request.schemeAndHttpHost }}{{ path(\'UcaWeb_preInscription\') }}\">ici</a>.<br />Une fois votre compte cr&eacute;&eacute;, connectez-vous, et cliquez sur le lien suivant : [[lienInscription]]&nbsp;pour vous permettre d&rsquo;ajouter l&rsquo;invitation &agrave; jouer &agrave; votre panier.<br />Validez votre panier pour confirmer votre inscription en tant que partenaire.<br /><br />En cas de probl&egrave;me, merci de contacter le bureau des sports.</p></div>', 'Inscription avec partenaire', 'InscriptionPartenaire'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Le [[date]], vous avez souhait&eacute; vous inscrire &agrave; l&#39;activit&eacute; suivante: [[inscription]]<br />Cette inscription a &eacute;t&eacute; refus&eacute;e pour le motif [[motifAnnulation]]. La pr&eacute;cision suivant a &eacute;t&eacute; indiqu&eacute;e: [[commentaireAnnulation]]</p></div>', 'Demande d\'inscription refusée', 'InscriptionRefusee'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Le [[date]], vous avez souhait&eacute; vous inscrire &agrave; l&#39;activit&eacute; suivante:[[inscription]]<strong> </strong><br />Votre inscription a &eacute;t&eacute; autoris&eacute;e. Vous devez maintenant acc&eacute;der &agrave; la page [[lienInscription]]&nbsp;afin de pouvoir ajouter votre inscription au panier et finaliser votre commande.</p><p>Attention, vous avez&nbsp;[[timerPanierApresValidation]] heure(s) pour ajouter votre inscription au panier. Apr&egrave;s l&#39;ajout au panier vous disposerez de&nbsp;[[timerPanier]] minutes pour soit payer votre commande en ligne, soit confirmer que vous paierez au bureau des sports. Vous finaliserez ainsi votre inscription. <strong>Si vous d&eacute;passez ces d&eacute;lais, votre inscription sera automatiquement annul&eacute;e.</strong></p><strong><strong> </strong></strong></div>', 'Demande d\'inscription validée', 'InscriptionValidee'), 
(NULL, '<p>Bonjour&nbsp;[[user]] !</p><p>&nbsp;</p><p>Pour valider votre compte utilisateur, merci de vous rendre sur [[lienPreInscription]]</p><p>&nbsp;</p><p>Ce lien ne peut &ecirc;tre utilis&eacute; qu&#39;une seule fois pour valider votre compte.</p>', 'Inscription confirmée', 'ConfirmationEmail'), 
(NULL, '<div>Bonjour,<p>Une demande de pr&eacute;-inscription a &eacute;t&eacute; faite par l&#39;utilisateur suivant : [[prenom]]&nbsp;[[nom]]</p><p>Pour aller consulter sa fiche, merci de vous rendre sur le lien suivant:&nbsp;[[lienUtilisateur]]</p></div>', 'Demande de validation', 'DemandeValidationEmail'), 
(NULL, '<p>Bonjour,</p><p>Votre demande de pr&eacute;-inscription a bien &eacute;t&eacute; prise en compte.</p>', 'Confirmation demande d\'inscription', 'PreInscriptionEmail'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Votre demande de pr&eacute;-inscription a &eacute;t&eacute; refus&eacute;e.</p></div>', 'Inscription refusée', 'RefusEmail'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>Le message suivant vous a &eacute;t&eacute; envoy&eacute; par l&#39;encadrant de l&#39;activit&eacute;:</p><p>[[message]]</p><p>&nbsp;</p></div>', '[[formatActivite]] : [[dateDebut]] - [[dateFin]] [[objet]]', 'MailPourTousLesInscripts'), 
(NULL, '<div>Bonjour,<br />&nbsp;<p>La commande n&deg;[[numeroCommande]]&nbsp;a &eacute;t&eacute; enregistr&eacute;e. Vous avez [[timerBds]]&nbsp;heures pour payer cette commande aupr&egrave;s du bureau des sports. Pass&eacute; ce d&eacute;lai, la commande sera annul&eacute;e.</p></div>', 'Commande à régler au bureau des sports', 'CommandeARegler'), 
(NULL, '<p>Bonjour,<br />&nbsp;</p><p>Vous avez une demande de contact de la part de :&nbsp;[[contact_from]]</p><p>Sujet :&nbsp;&nbsp;[[format_activite]]&nbsp;:&nbsp;[[event_date]]&nbsp;[[event_start_hour]]&nbsp;-&nbsp;[[event_date]]&nbsp;[[event_end_hour]]<br />Corps du message :<br />[[message]]</p>', '[[format_activite]] : [[event_date]] [[event_start_hour]] - [[event_date]] [[event_end_hour]]', 'ContactEncadrantEmail')


-- --------------------------------------------------------

--
-- Structure de la table `logo_parametrable`
--

DROP TABLE IF EXISTS `logo_parametrable`;
CREATE TABLE IF NOT EXISTS `logo_parametrable` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `emplacement` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1413 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `logo_parametrable`
--

INSERT INTO `logo_parametrable` (`id`, `image`, `description`, `updated_at`, `emplacement`) VALUES
(1, '63c133dc81db6370348499.png', 'Logo dans l\'entête de l\'application (prévoir une image qui passe sur un fond noir)', '2023-01-13 10:35:08', 'Entete'),
(2, '63c133c7321b7519875939.png', 'Logo dans le pied de page de l\'application (prévoir une image qui passe sur un fond noir)', '2023-01-13 10:34:47', 'Pied de page'),
(3, '63b555aa9abfd126623315.png', 'Logo utilisé dans les fichiers PDF générés (factures, avoirs, credits, ...), (prévoir une image qui passe sur un fond blanc)', '2023-01-04 10:32:10', 'PDF Générés'),
(4, '', 'Logo utilisé sur les écrans de connexion (prévoir une image qui passe sur un fond blanc)', NULL, 'Ecran de connexion'),
(5, '63b53ffb719a6655539984.png', 'Logo utilisé en signature des mails', '2023-01-04 08:59:39', 'Signature des mails'),
(6, '', 'Logo utilisé dans les export excel', NULL, 'Exports Excel'),
(7, '63b53f654b317001980524.png', 'Logo sur le carousel de la page d\'accueil', '2023-01-04 08:57:09', 'Caroussel Accueil'),
(8, '63c0002854bf4376360946.png', 'Icône des onglets du navigateur', '2023-01-12 12:42:16', 'favicon');

-- --------------------------------------------------------

--
-- Structure de la table `parametrage`
--

DROP TABLE IF EXISTS `parametrage`;
CREATE TABLE IF NOT EXISTS `parametrage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lien_facebook` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lien_instagram` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lien_youtube` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `mail_contact` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `timer_panier` int NOT NULL,
  `timer_cb` int NOT NULL,
  `timer_bds` int NOT NULL,
  `timer_paybox` int NOT NULL,
  `timer_panier_apres_validation` int NOT NULL,
  `annee_universitaire` int NOT NULL,
  `adresse_facturation` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `siret` bigint NOT NULL,
  `libelle_adresse` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `timer_partenaire` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `parametrage`
--

INSERT INTO `parametrage` (`id`, `lien_facebook`, `lien_instagram`, `lien_youtube`, `mail_contact`, `timer_panier`, `timer_cb`, `timer_bds`, `timer_paybox`, `timer_panier_apres_validation`, `annee_universitaire`, `adresse_facturation`, `siret`, `libelle_adresse`, `timer_partenaire`, `prefix_mail`, `signature_mail`) VALUES
(1, 'https://www.facebook.com/ucasportfr', 'https://www.instagram.com/uca_sport', 'https://www.youtube.com/channel/UCja2ce2s9HfnxcBjrhW6n0w/videos', 'sport@univ-cotedazur.fr', 30, 16, 48, 15, 72, 2022, '28 avenue Valrose 06100 nice', 13002566100013, "Université Côte d\'Azur", '2', '[UCA]', '<p><em>Ceci est une notification automatique d&#39;UCA, toute r&eacute;ponse sera ignor&eacute;e.<br />\r\nPage d&#39;accueil d&#39;UCA Sport : <a href=\"https://sport.univ-cotedazur.fr/fr/\" style=\"color:blue; text-decoration:underline\">https://sport.univ-cotedazur.fr/fr/ </a></em></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>');

-- --------------------------------------------------------

--
-- Structure de la table `style`
--

DROP TABLE IF EXISTS `style`;
CREATE TABLE IF NOT EXISTS `style` (
  `id` int NOT NULL AUTO_INCREMENT,
  `primary_color` varchar(7) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `preview` tinyint(1) NOT NULL,
  `primary_hover` double NOT NULL,
  `primary_shadow` double NOT NULL,
  `secondary_color` varchar(7) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `secondary_hover` double NOT NULL,
  `secondary_shadow` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `style`
--

INSERT INTO `style` (`id`, `primary_color`, `preview`, `primary_hover`, `primary_shadow`, `secondary_color`, `secondary_hover`, `secondary_shadow`, `success_color`, `success_hover`, `success_shadow`, `warning_color`, `warning_hover`, `warning_shadow`, `danger_color`, `danger_hover`, `danger_shadow`, `navbar_background_color`, `navbar_foreground_color`) VALUES
(1, '#f46e15', 0, 0.2, 0.05, '#350061', 0.55, 0.25, '#11C577', 0, 0, '#FFC107', 0, 0, '#AE1143', 0, 0, '#1a1a1a', '#ffffff'),
(2, '#f46e15', 1, 0.2, 0.05, '#350061', 0.55, 0.25, '#11C577', 0, 0, '#FFC107', 0, 0, '#AE1143', 0, 0, '#1a1a1a', '#ffffff');

INSERT INTO `shnu_rubrique` (`id`, `type_id`, `ordre`, `titre`, `lien`, `texte`, `image`, `updated_at`) VALUES (NULL, '3', '1', 'ÉTUDIANT / FUTUR ÉTUDIANT, CANDIDATEZ POUR LE STATUT DE SHN', 'https://shnu.univ-cotedazur.fr/', NULL, '5f16f773aa469580110728.png', '2022-11-11 09:00:00'), (NULL, '1', '2', 'NOS AMBASSADEURS', NULL, NULL, '5f16f88291e5d347161066.jpg', '2022-11-11 09:00:00'), (NULL, '4', '3', 'ACCOMPAGNEMENT', NULL, '<p><strong>Universit&eacute; C&ocirc;te d&rsquo;Azur</strong> s&rsquo;engage dans une politique volontariste d&rsquo;aide aux sportif.ve.s de haut niveau. L&rsquo;obtention du statut facilite la r&eacute;ussite du double objectif&nbsp;: concilier ses &eacute;tudes sup&eacute;rieures avec une carri&egrave;re sportive professionnelle ou semi professionnelle.&nbsp;</p>\r\n\r\n<p>Le <strong>statut de SHNU</strong> est accord&eacute; pour une ann&eacute;e universitaire sur avis de la commission SHNU et est renouvelable sans limite d&egrave;s lors que l&rsquo;on est &eacute;tudiant.e r&eacute;guli&egrave;rement inscrit.e &agrave; Universit&eacute; C&ocirc;te d&rsquo;Azur.</p>\r\n\r\n<p>Chaque &eacute;tudiant SHNU est suivi par un <strong>r&eacute;f&eacute;rent p&eacute;dagogique</strong>, qui l&rsquo;accompagne dans la r&eacute;ussite de son projet universitaire, au regard de ses contraintes sportives.</p>\r\n\r\n<p>Qu&rsquo;offre le statut SHNU&nbsp;&agrave; Universit&eacute; C&ocirc;te d&rsquo;Azur ?</p>\r\n\r\n<ul>\r\n <li>Un <strong>am&eacute;nagement des &eacute;tudes et des examens</strong> en lien avec les responsables de formation</li>\r\n <li>Un <strong>accompagnement personnalis&eacute;</strong> par le r&eacute;f&eacute;rent p&eacute;dagogique d&rsquo;Universit&eacute; C&ocirc;te d&rsquo;Azur</li>\r\n <li>Une <strong>alimentation diff&eacute;renci&eacute;e</strong> dans les points de restauration CROUS</li>\r\n <li>Un <strong>acc&egrave;s facilit&eacute; aux logements &eacute;tudiants</strong> CROUS</li>\r\n <li>Une <strong>valorisation</strong> par les services de communication d&rsquo;Universit&eacute; C&ocirc;te d&rsquo;Azur</li>\r\n <li>L&rsquo;&eacute;ligibilit&eacute; aux <strong>bourses r&eacute;serv&eacute;es aux sportifs de haut niveau</strong> par la <a href=\"https://fondation-uca.org\">Fondation UCA</a></li>\r\n</ul>\r\n\r\n<p>Comment obtenir le statut ?&nbsp;</p>\r\n\r\n<ul>\r\n <li>Rendez-vous sur le bouton &quot;candidater&quot; de la page Sport de Haut Niveau</li>\r\n</ul>', '5f72dd00d32fd045103120.png', '2022-11-11 09:00:00'), (NULL, '2', '4', 'PARTENAIRES', NULL, NULL, '5f43c2f5d4a12228211632.png', '2022-11-11 09:00:00')


COMMIT;

