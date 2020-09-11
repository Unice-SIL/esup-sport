-- Modification libéllé:	
UPDATE `utilisateur_credit_historique` 
SET `operation` = "Génération d\'avoir"  
WHERE `utilisateur_credit_historique`.`operation` = "génération d\'avoir";
