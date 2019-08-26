/* DTI - 24/07/2019 - Ajout de messages sur les comportements pour affichage du panier */
UPDATE `comportement_autorisation`
set `description_comportement` = 'Obligatoire pour s''inscrire aux activités'
where `code_comportement` = 'cotisation';
update `comportement_autorisation`
set `description_comportement` = 'Achat de carte'
where `code_comportement` = 'carte'

INSERT INTO `comportement_autorisation` (`id`, `libelle`, `code_comportement`, `description_comportement`) VALUES
(6, 'Validation par un gestionnaire', 'validationgestionnaire', null);

INSERT INTO `image_fond` (`id`, `emplacement`, `titre`, `image`, `updated_at`) VALUES
(13, 'Défaut', 'Défaut', '5d259ed305af6206985456.png', '2019-06-25 08:34:35');

/* La commande de génération des traductions sera probablement à lancer */ 

/* Ajout de la traduction */
INSERT INTO `ext_translations` (`locale`,`object_class`,`field`,`foreign_key`,`content`) VALUES ('en','UcaBundle\\Entity\\ComportementAutorisation','descriptionComportement','1','Required for any registration to the activities');
INSERT INTO `ext_translations` (`locale`,`object_class`,`field`,`foreign_key`,`content`) VALUES ('en','UcaBundle\\Entity\\ComportementAutorisation','descriptionComportement','4','Buying Card');

-- Problème détecté en Recette - L'affichage d'un planning retourne une erreur SQL (à propos de la commande GROUP BY) 
--   * Dans PHPMyAdmin - Aller sur l'onglet "Variables"                                               
--   * S'assurer que la variable "sql mode" ne contienne pas la valeur ONLY_FULL_GROUP_BY 
--   * Supprimer cette valeur de la liste sinon                                          

/* Remplir la table des dhtmlx_date pour chaque FormatSimple */
INSERT INTO dhtmlx_date(date_debut, date_fin, format, dependance_serie, description, format_simple_id)
SELECT date_debut_effective, date_fin_effective, 'DhtmlxEvenement', 0, libelle, id
FROM format_activite 
WHERE format = 'FormatSimple';

INSERT INTO `groupe` (`id`, `name`, `roles`) VALUES
(5, 'Gestionnaire de caisse', 'a:1:{i:0;s:30:\"ROLE_GESTION_PAIEMENT_COMMANDE\";}');

/* Prévoir une requête d'update des groupes pour leur rajouter les bons droits récemment créés */

/* Ajout du paramétrage */
INSERT INTO `parametrage` (`id`, `lien_facebook`, `lien_instagram`, `lien_youtube`, `mail_contact`, `timer_panier`, `timer_cb`, `timer_bds`, `timer_paybox`) VALUES
(1, 'http://facebook.uca.fr', 'http://instagram.uca.fr', 'http://youtube.uca.fr', 'contact@uca.fr', 30, 20, 48, 3);

/* Attention, il faudra voir le contenu de la table  */ 
-- INSERT INTO `statut_utilisateur` VALUES (1,'compte valide'),(2,'compte en attente de validation'),(3,'Inscription refusée'),(4,'Compte bloqué');