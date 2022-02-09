DROP EVENT IF EXISTS `SuppressionDesCartesInvalides`;
DELIMITER $$
CREATE DEFINER=`root`@`localhost` EVENT `SuppressionDesCartesInvalides` ON SCHEDULE EVERY 1 DAY STARTS '2020-09-01 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN

    DROP TABLE IF EXISTS tmp_utilisateur_type_autorisation;
    CREATE TABLE tmp_utilisateur_type_autorisation AS
    SELECT 
        uta.utilisateur_id as utilisateur_id, uta.type_autorisation_id as type_autorisation_id
    FROM 
        utilisateur_type_autorisation uta
    INNER JOIN type_autorisation ta
        ON ta.id = uta.type_autorisation_id
        AND ta.comportement_id = 4
    ;

    SELECT 
        remove_type_autorisation_carte(tmp_uta.utilisateur_id, tmp_uta.type_autorisation_id) 
    FROM 
        tmp_utilisateur_type_autorisation tmp_uta
    ;

    DROP TABLE IF EXISTS tmp_utilisateur_type_autorisation;
END$$

DELIMITER ;