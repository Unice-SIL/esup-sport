DELETE FROM `commande_detail_commande_detail`;

DELETE FROM `commande_detail`;

DELETE FROM `commande`;

DELETE FROM `autorisation`;

DELETE FROM `inscription`;

DELETE FROM `inscription_utilisateur`;

DELETE FROM `utilisateur_type_autorisation`
WHERE type_autorisation_id <> 2;

DELETE FROM `utilisateur_type_autorisation`
WHERE utilisateur_id NOT IN (
    SELECT id
    FROM `utilisateur` 
    WHERE profil_id = 4 /* Etudiants  */
);