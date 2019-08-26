INSERT INTO dhtmlx_date(date_debut, date_fin, format, dependance_serie, description, format_simple_id)
SELECT date_debut_effective, date_fin_effective, 'DhtmlxEvenement', 0, libelle, id
FROM format_activite 
WHERE format = 'FormatSimple';

INSERT INTO `groupe` (`id`, `name`, `roles`) VALUES
(5, 'Gestionnaire de caisse', 'a:1:{i:0;s:30:\"ROLE_GESTION_PAIEMENT_COMMANDE\";}');

INSERT INTO `parametrage` (`id`, `lien_facebook`, `lien_instagram`, `lien_youtube`, `mail_contact`, `timer_panier`, `timer_cb`, `timer_bds`, `timer_paybox`) VALUES
(1, 'http://facebook.uca.fr', 'http://instagram.uca.fr', 'http://youtube.uca.fr', 'damien.tinseau@acatus.fr', 30, 20, 48, 3);
