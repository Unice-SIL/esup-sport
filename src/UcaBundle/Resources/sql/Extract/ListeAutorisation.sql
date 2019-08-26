SELECT 
	tau.id,
	co.libelle AS Comportement,
	tau.libelle AS Autorisation,
    tau.informations_complementaires AS InfoComplementaire,
    t.libelle AS Tarif
FROM type_autorisation tau
LEFT JOIN tarif t
	ON t.id = tau.tarif_id
LEFT JOIN comportement_autorisation co
	ON co.id = tau.comportement_id
ORDER BY co.libelle, tau.libelle;