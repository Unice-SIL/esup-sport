DELIMITER $$ 
    DROP FUNCTION IF EXISTS `remove_type_autorisation_carte`$$
    CREATE FUNCTION `remove_type_autorisation_carte`(X INT, Y INT) RETURNS INT
    BEGIN 

    SET @UserId = X ;
    SET @CarteId = Y;

    SET @MaxDateCarteFinValidite = (
        SELECT 
            max(cd.date_carte_fin_validite) 
        FROM 
            commande_detail cd 
        INNER JOIN 
            commande c 
            ON 
            c.id = cd.commande_id 
            AND 
            c.utilisateur_id = @UserId
            AND 
            c.statut = 'termine'
        WHERE
            cd.type_autorisation_id = @CarteId
        
    );

    IF @MaxDateCarteFinValidite IS NOT NULL AND @MaxDateCarteFinValidite < NOW() THEN
        DELETE FROM utilisateur_type_autorisation WHERE utilisateur_id = @UserId AND type_autorisation_id = @CarteId;

        RETURN 1;
    END IF;
    RETURN 0;
    END$$

DELIMITER ;