SELECT 
	fa.id,
    fa.libelle AS FormatActivite,
	rs.libelle AS Ressource,
    dhd.date_debut,
    dhd.date_fin,
    dhd.description
FROM format_activite fa
JOIN format_activite_lieu fal
	ON fal.format_activite_id = fa.id
JOIN ressource rs
	ON rs.id = fal.lieu_id
JOIN reservabilite rb
	ON rb.ressource_id = rs.id
JOIN dhtmlx_date dhd
	ON dhd.reservabilite_id = rb.id
    AND dhd.format = 'DhtmlxEvenement'
WHERE fa.format = 'FormatAvecReservation'