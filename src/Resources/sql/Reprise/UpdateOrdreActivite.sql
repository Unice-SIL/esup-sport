UPDATE `activite` a
INNER JOIN (
	SELECT id
		,ROW_NUMBER() OVER (
			ORDER BY id
			) AS nbre
	FROM `activite`
	ORDER BY id
	) subquery ON a.id = subquery.id

SET a.`ordre` = subquery.nbre
