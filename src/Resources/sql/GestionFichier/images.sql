SELECT name FROM 
(
{{ FileImageList }}
) t
WHERE name NOT IN ( SELECT ''
	UNION SELECT image FROM `activite`
	UNION SELECT image FROM `actualite`
	UNION SELECT image FROM `classe_activite`
	UNION SELECT image FROM `etablissement`
	UNION SELECT image FROM `format_activite`
	UNION SELECT image FROM `image_fond`
	UNION SELECT image FROM `ressource`
)
ORDER BY name;
