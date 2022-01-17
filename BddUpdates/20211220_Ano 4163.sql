-------------------------------------------------------------------
-- Gestion des données sur le graphique des inscrits par profils --
-------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_calcul_nb_user_by_profil$$
CREATE PROCEDURE sp_calcul_nb_user_by_profil()
BEGIN
    DELETE
    FROM nb_user_by_element
    WHERE type = 1;

    INSERT INTO nb_user_by_element (
        libelle
        ,nombre_user
        ,type
        )
    SELECT profil_utilisateur.libelle
        ,count(utilisateur.id)
        ,1
    FROM uca.profil_utilisateur
    LEFT JOIN uca.utilisateur ON utilisateur.profil_id = profil_utilisateur.id
        AND utilisateur.enabled = 1
    GROUP BY profil_utilisateur.id;
END$$
DELIMITER ;

------------------------------------------------------------------
-- Gestion des données sur le graphique des inscrits par niveau --
------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_calcul_nb_user_by_niveau$$
CREATE PROCEDURE sp_calcul_nb_user_by_niveau()
BEGIN
    DELETE
    FROM nb_user_by_element
    WHERE type = 2;

    INSERT INTO nb_user_by_element (
        libelle
        ,nombre_user
        ,type
        )
    SELECT data_utilisateur.niveau
        ,count(utilisateur.id)
        ,2
    FROM data_utilisateur
    LEFT JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
        AND utilisateur.enabled = 1
    WHERE est_membre_personnel = 0
        AND annee_universitaire IN (
            SELECT parametrage.annee_universitaire
            FROM uca.parametrage
            )
    GROUP BY data_utilisateur.niveau;
END$$
DELIMITER ;

------------------------------------------------------------------------------
-- Gestion des données sur le graphique des connexion par horaire et profil --
------------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_calcul_nb_user_connexion_by_horaire_and_profil$$
CREATE PROCEDURE sp_calcul_nb_user_connexion_by_horaire_and_profil()
BEGIN
    DECLARE annee_universitaire_parametre int(4);
	SELECT parametrage.annee_universitaire
    INTO annee_universitaire_parametre
    FROM uca.parametrage;

    DELETE
    FROM nb_user_by_horaire_and_element
    WHERE type = 1;

    INSERT INTO nb_user_by_horaire_and_element (
        libelle
        ,horaire
        ,nombre_user
        ,type
        )
        SELECT profil_utilisateur.libelle
        ,CASE 
            WHEN hour(log_connexion.date_connexion) >= 20
                THEN '+20'
            WHEN hour(log_connexion.date_connexion) >= 17
                THEN '17-19'
            WHEN hour(log_connexion.date_connexion) >= 14
                THEN '14-16'
            WHEN hour(log_connexion.date_connexion) >= 12
                THEN '12-13'
            WHEN hour(log_connexion.date_connexion) >= 9
                THEN '09-11'
            WHEN hour(log_connexion.date_connexion) >= 7
                THEN '07-08'
            WHEN hour(log_connexion.date_connexion) >= 0
                THEN '-07'
            ELSE log_connexion.date_connexion
            END AS horaire
        ,count(data_utilisateur.id)
        ,1
    FROM data_utilisateur
    INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
        AND utilisateur.enabled = 1
	INNER JOIN uca.profil_utilisateur ON utilisateur.profil_id = profil_utilisateur.id
    LEFT JOIN uca.log_connexion ON utilisateur.id = log_connexion.utilisateur_id 
        AND year(log_connexion.date_connexion) >= annee_universitaire_parametre
    WHERE annee_universitaire = annee_universitaire_parametre
    GROUP BY profil_utilisateur.libelle
        ,horaire
    ORDER BY hour(log_connexion.date_connexion);
END$$
DELIMITER ;

----------------------------------------------------------------------------
-- Gestion des données sur le graphique des connexion par horaire et age --
----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_calcul_nb_user_connexion_by_horaire_and_age$$
CREATE PROCEDURE sp_calcul_nb_user_connexion_by_horaire_and_age()
BEGIN
	DECLARE annee_universitaire_parametre int(4);
	SELECT parametrage.annee_universitaire
    INTO annee_universitaire_parametre
    FROM uca.parametrage;

    DELETE
    FROM nb_user_by_horaire_and_element
    WHERE type = 2;

    INSERT INTO nb_user_by_horaire_and_element (
        libelle
        ,horaire
        ,nombre_user
        ,type
        )
    SELECT CASE 
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 50
                THEN '+50'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 40
                THEN '40-49'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 30
                THEN '30-39'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 20
                THEN '20-29'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 0
                THEN '-20'
            ELSE NULL
            END AS age
        ,CASE 
            WHEN hour(log_connexion.date_connexion) >= 20
                THEN '+20'
            WHEN hour(log_connexion.date_connexion) >= 17
                THEN '17-19'
            WHEN hour(log_connexion.date_connexion) >= 14
                THEN '14-16'
            WHEN hour(log_connexion.date_connexion) >= 12
                THEN '12-13'
            WHEN hour(log_connexion.date_connexion) >= 9
                THEN '09-11'
            WHEN hour(log_connexion.date_connexion) >= 7
                THEN '07-08'
            WHEN hour(log_connexion.date_connexion) >= 0
                THEN '-07'
            ELSE log_connexion.date_connexion
            END AS horaire
        ,count(data_utilisateur.id)
        ,2
    FROM data_utilisateur
    INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
        AND utilisateur.enabled = 1
    LEFT JOIN uca.log_connexion ON utilisateur.id = log_connexion.utilisateur_id
        AND year(log_connexion.date_connexion) >= annee_universitaire_parametre
    WHERE annee_universitaire = annee_universitaire_parametre
    GROUP BY age
        ,horaire
    ORDER BY hour(log_connexion.date_connexion);
END$$
DELIMITER ;

-----------------------------------------------------------------------------
-- Gestion des données sur le graphique des connexion par horaire et genre --
-----------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_calcul_nb_user_connexion_by_horaire_and_genre$$
CREATE PROCEDURE sp_calcul_nb_user_connexion_by_horaire_and_genre()
BEGIN
	DECLARE annee_universitaire_parametre int(4);
	SELECT parametrage.annee_universitaire
    INTO annee_universitaire_parametre
    FROM uca.parametrage;

    DELETE
    FROM nb_user_by_horaire_and_element
    WHERE type = 3;

    INSERT INTO nb_user_by_horaire_and_element (
        libelle
        ,horaire
        ,nombre_user
        ,type
        )
    SELECT data_utilisateur.sexe
        ,CASE 
            WHEN hour(log_connexion.date_connexion) >= 20
                THEN '+20'
            WHEN hour(log_connexion.date_connexion) >= 17
                THEN '17-19'
            WHEN hour(log_connexion.date_connexion) >= 14
                THEN '14-16'
            WHEN hour(log_connexion.date_connexion) >= 12
                THEN '12-13'
            WHEN hour(log_connexion.date_connexion) >= 9
                THEN '09-11'
            WHEN hour(log_connexion.date_connexion) >= 7
                THEN '07-08'
            WHEN hour(log_connexion.date_connexion) >= 0
                THEN '-07'
            ELSE log_connexion.date_connexion
            END AS horaire
        ,count(data_utilisateur.id)
        ,3
    FROM data_utilisateur
    INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
        AND utilisateur.enabled = 1
    LEFT JOIN uca.log_connexion ON utilisateur.id = log_connexion.utilisateur_id 
        AND year(log_connexion.date_connexion) >= annee_universitaire_parametre
    WHERE annee_universitaire = annee_universitaire_parametre
    GROUP BY data_utilisateur.sexe
        ,horaire
    ORDER BY hour(log_connexion.date_connexion);
END$$
DELIMITER ;

----------------------------------------------------------
-- Gestion des données sur le graphique de genre et age --
----------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_calcul_nb_user_by_age_and_genre$$
CREATE PROCEDURE sp_calcul_nb_user_by_age_and_genre()
BEGIN
	DECLARE annee_universitaire_parametre int(4);
	SELECT parametrage.annee_universitaire
    INTO annee_universitaire_parametre
    FROM uca.parametrage;

    DELETE
    FROM nb_user_by_genre_and_age;

    INSERT INTO nb_user_by_genre_and_age (
        genre
        ,age
        ,nombre_user
        )
    SELECT data_utilisateur.sexe
        ,CASE 
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 50
                THEN '+50'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 40
                THEN '40-49'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 30
                THEN '30-39'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 20
                THEN '20-29'
            WHEN year(now()) - SUBSTRING_INDEX(dateNaissance, '/', - 1) >= 0
                THEN '-20'
            ELSE NULL
            END AS age
        ,count(data_utilisateur.id)
    FROM data_utilisateur
    -- INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
    -- 	AND utilisateur.enabled = 1
    WHERE annee_universitaire = annee_universitaire_parametre
    GROUP BY data_utilisateur.sexe
        ,age
    ORDER BY data_utilisateur.sexe, SUBSTRING_INDEX(dateNaissance, '/', - 1);
END$$
DELIMITER ;

------------------------------------------------------------
-- Gestion des données sur les KPI généraux des étudiants --
------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS update_kpi_generaux_etudiant$$
CREATE PROCEDURE update_kpi_generaux_etudiant(IN annee INT(4))
BEGIN
	DELETE
    FROM kpi_generaux_etudiants
	WHERE annee_universitaire = annee;
	
	INSERT INTO kpi_generaux_etudiants (annee_universitaire)
	VALUES (annee);

	UPDATE kpi_generaux_etudiants
	SET nb_etudiants = (
			SELECT count(DISTINCT id)
			FROM data_utilisateur
			WHERE annee_universitaire = annee
				AND est_membre_personnel = 0
			)
	WHERE annee_universitaire = annee;

	UPDATE kpi_generaux_etudiants
	SET nb_inscrits = (
			SELECT count(utilisateur.id)
			FROM data_utilisateur
			INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
				AND utilisateur.enabled = 1
			WHERE est_membre_personnel = 0
				AND annee_universitaire = annee
			)
	WHERE annee_universitaire = annee;

	UPDATE kpi_generaux_etudiants
	SET nb_boursier = (
			SELECT count(utilisateur.id)
			FROM data_utilisateur
			INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
				AND utilisateur.enabled = 1
			WHERE est_membre_personnel = 0
				AND boursier = 'O'
				AND annee_universitaire = annee
			)
	WHERE annee_universitaire = annee;

	UPDATE kpi_generaux_etudiants
	SET nb_shnu = (
			SELECT count(utilisateur.id)
			FROM data_utilisateur
			INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
				AND utilisateur.enabled = 1
			WHERE est_membre_personnel = 0
				AND shnu = 'O'
				AND annee_universitaire = annee
			)
	WHERE annee_universitaire = annee;
END$$
DELIMITER ;

-------------------------------------------------------------
-- Gestion des données sur les KPI généraux des personnels --
-------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS update_kpi_generaux_personnels$$
CREATE PROCEDURE update_kpi_generaux_personnels(IN annee INT(4))
BEGIN
	DELETE
    FROM kpi_generaux_personnels
	WHERE annee_universitaire = annee;
	
	INSERT INTO kpi_generaux_personnels (annee_universitaire)
	VALUES (annee);

	UPDATE kpi_generaux_personnels
	SET nb_personnels = (
			SELECT count(DISTINCT id)
			FROM data_utilisateur
			WHERE annee_universitaire = annee
				AND est_membre_personnel = 1
			)
	WHERE annee_universitaire = annee;

	UPDATE kpi_generaux_personnels
	SET nb_inscrits = (
			SELECT count(utilisateur.id)
			FROM data_utilisateur
			INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
				AND utilisateur.enabled = 1
			WHERE est_membre_personnel = 1
				AND annee_universitaire = annee
			)
	WHERE annee_universitaire = annee;

	UPDATE kpi_generaux_personnels
	SET nb_cat_a = (
			SELECT count(utilisateur.id)
			FROM data_utilisateur
			INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
				AND utilisateur.enabled = 1
			WHERE est_membre_personnel = 1
				AND categorie = 'CAT_A'
				AND annee_universitaire = annee
			)
	WHERE annee_universitaire = annee;

    UPDATE kpi_generaux_personnels
	SET nb_cat_b = (
			SELECT count(utilisateur.id)
			FROM data_utilisateur
			INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
				AND utilisateur.enabled = 1
			WHERE est_membre_personnel = 1
				AND categorie = 'CAT_B'
				AND annee_universitaire = annee
			)
	WHERE annee_universitaire = annee;

    UPDATE kpi_generaux_personnels
	SET nb_cat_c = (
			SELECT count(utilisateur.id)
			FROM data_utilisateur
			INNER JOIN uca.utilisateur ON utilisateur.matricule = data_utilisateur.codEtu
				AND utilisateur.enabled = 1
			WHERE est_membre_personnel = 1
				AND categorie = 'CAT_C'
				AND annee_universitaire = annee
			)
	WHERE annee_universitaire = annee;
END$$
DELIMITER ;

-----------------------------------------------
-- Gestion des données sur les KPI généraux  --
-----------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS update_kpi_generaux$$
CREATE PROCEDURE update_kpi_generaux(IN annee INT(4))
BEGIN
	DECLARE annee_universitaire_parametre int(4);
	SELECT parametrage.annee_universitaire
    INTO annee_universitaire_parametre
    FROM uca.parametrage;
	
	call update_kpi_generaux_etudiant(annee_universitaire_parametre);
	call update_kpi_generaux_personnels(annee_universitaire_parametre);
END$$
DELIMITER ;

-----------------------------------------------------
-- Evènement pour la mise à jour des statistiques  --
-----------------------------------------------------
DELIMITER $$
DROP EVENT IF EXISTS CalculStatistiques$$
CREATE EVENT CalculStatistiques
    ON SCHEDULE EVERY 1 DAY STARTS '2021-12-20 00:05:00'
    ON COMPLETION PRESERVE
DO BEGIN
	call sp_calcul_nb_user_by_profil(); 
	call sp_calcul_nb_user_by_niveau(); 
	call sp_calcul_nb_user_connexion_by_horaire_and_profil(); 
	call sp_calcul_nb_user_connexion_by_horaire_and_age(); 
	call sp_calcul_nb_user_connexion_by_horaire_and_genre(); 
	call sp_calcul_nb_user_by_age_and_genre();
    call update_kpi_generaux();
END $$
DELIMITER ;



call sp_calcul_nb_user_by_profil(); 
call sp_calcul_nb_user_by_niveau(); 
call sp_calcul_nb_user_connexion_by_horaire_and_profil(); 
call sp_calcul_nb_user_connexion_by_horaire_and_age(); 
call sp_calcul_nb_user_connexion_by_horaire_and_genre(); 
call sp_calcul_nb_user_by_age_and_genre();
call update_kpi_generaux_etudiant(2019);
call update_kpi_generaux_etudiant(2020);
call update_kpi_generaux_etudiant(2021);
call update_kpi_generaux_personnels(2019);
call update_kpi_generaux_personnels(2020);
call update_kpi_generaux_personnels(2021);