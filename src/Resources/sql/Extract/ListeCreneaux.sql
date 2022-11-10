SELECT 
	fa.id,
	fa.libelle AS FormatActivite,
    t.libelle AS Tarif,
    dhd.date_debut,
    dhd.date_fin,
    dhd.description
FROM format_activite fa
LEFT JOIN creneau c
	ON c.format_activite_id = fa.id
LEFT JOIN dhtmlx_date dhs
	ON dhs.creneau_id = c.id
LEFT JOIN dhtmlx_date dhd
	ON dhd.serie_id = dhs.id
LEFT JOIN tarif t
	ON t.id = c.tarif_id
WHERE fa.format = 'FormatAvecCreneau'