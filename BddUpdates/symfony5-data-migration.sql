-- mise à jour role utilisateur :
update utilisateur set roles = '[]';

-- mise à jour des annotations en bdd :
update `ext_annotation` set entity = REPLACE(entity, SUBSTRING_INDEX(entity, '\\', 2), 'App\\Entity\\Uca');

-- mise à jour des groupes :
update groupe set roles = '["ROLE_GESTION_ACTIVITE_ECRITURE","ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE","ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE","ROLE_GESTION_CRENEAU_ECRITURE","ROLE_GESTION_PROFIL_UTILISATEUR_LECTURE","ROLE_GESTION_TARIF_LECTURE","ROLE_GESTION_TEXTE_LECTURE","ROLE_GESTION_TRADUCTION_LECTURE","ROLE_GESTION_LOG_LECTURE","ROLE_GESTION_TYPE_AUTORISATION_ECRITURE","ROLE_GESTION_TYPE_ACTIVITE_ECRITURE","ROLE_PREVISUALISATION"]' where id = 1;
update groupe set roles = '["ROLE_GESTION_TARIF_ECRITURE","ROLE_GESTION_ACTIVITE_LECTURE","ROLE_GESTION_FORMAT_ACTIVITE_LECTURE","ROLE_GESTION_CLASSE_ACTIVITE_LECTURE","ROLE_GESTION_TYPE_ACTIVITE_LECTURE","ROLE_GESTION_CRENEAU_LECTURE","ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE","ROLE_GESTION_TYPE_AUTORISATION_ECRITURE","ROLE_GESTION_UTILISATEUR_LECTURE","ROLE_GESTION_ETABLISSEMENT_LECTURE","ROLE_GESTION_RESSOURCE_LECTURE","ROLE_GESTION_LOG_LECTURE","ROLE_GESTION_COMMANDES","ROLE_PREVISUALISATION","ROLE_GESTION_CREDIT_UTILISATEUR_ECRITURE","ROLE_GESTION_AVOIR"]' where id = 2;
update groupe set roles = '["ROLE_GESTION_ACTIVITE_LECTURE","ROLE_GESTION_CLASSE_ACTIVITE_LECTURE","ROLE_GESTION_CRENEAU_LECTURE","ROLE_GESTION_ETABLISSEMENT_LECTURE","ROLE_GESTION_FORMAT_ACTIVITE_LECTURE","ROLE_GESTION_LOG_LECTURE","ROLE_GESTION_RESSOURCE_LECTURE","ROLE_ENCADRANT"]' where id = 3;
update groupe set roles = '["ROLE_GESTION_ACTIVITE_ECRITURE","ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE","ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE","ROLE_GESTION_TYPE_ACTIVITE_ECRITURE","ROLE_GESTION_CRENEAU_ECRITURE","ROLE_GESTION_TARIF_ECRITURE","ROLE_GESTION_UTILISATEUR_ECRITURE","ROLE_GESTION_TYPE_AUTORISATION_ECRITURE","ROLE_GESTION_RESSOURCE_ECRITURE","ROLE_GESTION_ETABLISSEMENT_ECRITURE","ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE","ROLE_GESTION_TEXTE_ECRITURE","ROLE_GESTION_TRADUCTION_ECRITURE","ROLE_GESTION_LOG_LECTURE","ROLE_GESTION_GROUPE_ECRITURE","ROLE_GESTION_IMAGEFOND_ECRITURE","ROLE_GESTION_ACTUALITE_ECRITURE","ROLE_GESTION_PARAMETRAGE","ROLE_PREVISUALISATION","ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION","ROLE_GESTION_COMMANDES","ROLE_GESTION_TEXTE_LECTURE","ROLE_GESTION_INSCRIPTION","ROLE_GESTION_EMAILING","ROLE_GESTION_EXTRACTION"]' where id = 4;
update groupe set roles = '["ROLE_GESTION_PAIEMENT_COMMANDE","ROLE_GESTION_COMMANDES","ROLE_GESTION_INSCRIPTION"]' where id = 5;
update groupe set roles = '["ROLE_GESTION_COMMANDES","ROLE_GESTION_INSCRIPTION","ROLE_GESTION_PAIEMENT_COMMANDE"]' where id = 12;
update groupe set roles = '["ROLE_GESTION_BASCULE","ROLE_GESTION_EMAILING","ROLE_GESTION_HIGHLIGHT_ECRITURE","ROLE_GESTION_LOGOPARTENAIRE_ECRITURE","ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE","ROLE_GESTION_FORMAT_ACTIVITE_LECTURE","ROLE_GESTION_GROUPE_ECRITURE","ROLE_GESTION_HIGHLIGHT_LECTURE","ROLE_GESTION_LOGOPARTENAIRE_LECTURE","ROLE_GESTION_SHNU_HIGHLIGHT_LECTURE","ROLE_GESTION_TEXTE_ECRITURE","ROLE_GESTION_EXTRACTION","ROLE_GESTION_ACTIVITE_ECRITURE","ROLE_GESTION_AVOIR"]' where id = 13;
update groupe set roles = '["ROLE_GESTION_ACTIVITE_ECRITURE","ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE","ROLE_GESTION_CRENEAU_ECRITURE","ROLE_GESTION_EMAILING","ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE","ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE","ROLE_GESTION_TEXTE_ECRITURE","ROLE_GESTION_TRADUCTION_ECRITURE"]' where id = 15;
