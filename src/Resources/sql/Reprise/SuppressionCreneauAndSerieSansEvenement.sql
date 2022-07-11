DROP TABLE

IF EXISTS `suppression_crenneau`;
	CREATE TABLE

IF NOT EXISTS `suppression_crenneau`(`id_creneau` INT (11) NOT NULL, `id_serie` INT (11) NOT NULL) ENGINE = MyISAM DEFAULT CHARSET = latin1;
	INSERT INTO `suppression_crenneau` (
		`id_creneau`
		,`id_serie`
		)
	SELECT c.id
		,serie.id
	FROM creneau c
	INNER JOIN format_activite fa ON c.format_activite_id = fa.id
		AND fa.format = 'FormatAvecCreneau'
	INNER JOIN dhtmlx_date serie ON c.id = serie.creneau_id
		AND serie.format = 'DhtmlxSerie'
	LEFT JOIN dhtmlx_date evenement ON serie.id = evenement.serie_id
		AND evenement.format = 'DhtmlxEvenement'
	WHERE evenement.id IS NULL;

DELETE
FROM `dhtmlx_date`
WHERE `id` IN (
		SELECT id_serie
		FROM suppression_crenneau
		);

DELETE
FROM `creneau_profil_utilisateur`
WHERE `creneau_id` IN (
		SELECT id_creneau
		FROM suppression_crenneau
		);

DELETE
FROM `creneau`
WHERE `id` IN (
		SELECT id_creneau
		FROM suppression_crenneau
		);

DROP TABLE

IF EXISTS `suppression_crenneau`;
