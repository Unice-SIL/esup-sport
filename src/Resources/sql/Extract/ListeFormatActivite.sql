SELECT
	tac.libelle AS TypeActivite,
	ca.libelle AS ClasseActivite,
	a.libelle AS Activite,
	fa.libelle AS FormatActivite,
    format AS Type,
	fa.est_payant AS Payant,
	t.libelle AS TarifFormat,
    autorisations.Autorisations,
    ns.NiveauSportif
FROM type_activite tac
INNER JOIN classe_activite ca
	ON tac.id = ca.type_activite_id
INNER JOIN activite a
	ON a.classe_activite_id = ca.id
INNER JOIN format_activite fa
	ON fa.activite_id = a.id
LEFT JOIN tarif t
	ON fa.tarif_id = t.id
LEFT JOIN (
	SELECT fa.id, 
		CONCAT('{ ',GROUP_CONCAT(tau.Autorisation SEPARATOR ', '),' }') as Autorisations
	FROM format_activite fa
	LEFT JOIN format_activite_type_autorisation fata
		ON fata.format_activite_id = fa.id
	LEFT JOIN (
		SELECT 
			tau.id,
			CONCAT(co.libelle, ' - ', tau.libelle) AS Autorisation
		FROM type_autorisation tau
		LEFT JOIN comportement_autorisation co
			ON co.id = tau.comportement_id
		ORDER BY co.libelle, tau.libelle
	) tau
		ON fata.type_autorisation_id = tau.id
	GROUP BY fa.id
) autorisations
	ON autorisations.id = fa.id
LEFT JOIN (
	SELECT 
		fa.id,
		GROUP_CONCAT(ns.libelle SEPARATOR ', ') AS NiveauSportif
	FROM format_activite fa
	LEFT JOIN format_activite_niveau_sportif fans
		ON fans.format_activite_id = fa.id
	LEFT JOIN niveau_sportif ns
		ON ns.id = fans.niveau_sportif_id
	GROUP BY fa.id
) ns
	ON ns.id = fa.id
ORDER BY 
	tac.libelle,
	ca.libelle,
	a.libelle,
	fa.libelle 