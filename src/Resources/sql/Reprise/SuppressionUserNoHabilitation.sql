DELETE FROM utilisateur 
WHERE id NOT IN (
	SELECT utilisateur_id
    FROM utilisateur_groupe
);