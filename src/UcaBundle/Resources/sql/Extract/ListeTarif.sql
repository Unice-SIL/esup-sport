SELECT 
	t.id, 
	t.libelle AS Libelle,
	CONCAT('{', GROUP_CONCAT(CONCAT(COALESCE(p.libelle, ''),': ',COALESCE(mtpu.montant, ''), ' €')), '}') AS Tarif
FROM tarif t
LEFT JOIN montant_tarif_profil_utilisateur mtpu
	ON mtpu.tarif_id = t.id
LEFT JOIN profil_utilisateur p
	ON mtpu.profil_id = p.id
GROUP BY t.id
ORDER BY t.libelle;