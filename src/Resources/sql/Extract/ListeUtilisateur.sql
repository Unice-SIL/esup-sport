SELECT 
	u.username, 
    pu.libelle AS TypeUtilisateur, 
    GROUP_CONCAT(g.libelle SEPARATOR ', ') AS Groupes 
FROM utilisateur u
JOIN profil_utilisateur pu
	ON u.profil_id = pu.id
LEFT JOIN utilisateur_groupe ug
	ON ug.utilisateur_id = u.id
LEFT JOIN groupe g
	ON ug.groupe_id = g.id
GROUP BY u.id
ORDER BY u.username;