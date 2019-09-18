/*
SELECT 
	dd.*,
    CONCAT(DATE_FORMAT(date_debut, '%Y-%m-%d'), DATE_FORMAT(date_fin, ' %T')),
    DATEDIFF(date_fin, date_debut),
    TIMEDIFF(date_fin, date_debut)
FROM dhtmlx_date dd 
*/
UPDATE dhtmlx_date dd 
SET date_fin = CONCAT(DATE_FORMAT(date_debut, '%Y-%m-%d'), DATE_FORMAT(date_fin, ' %T'))
WHERE serie_id IS NOT NULL
AND format = 'DhtmlxEvenement'
AND DATEDIFF(date_fin, date_debut) > 0
AND DATE_FORMAT(date_fin, '%T') > DATE_FORMAT(date_debut, '%T');