update
    creneau_profil_utilisateur cpu
set
    cpu.nb_inscrits = (
        select 
            count(*) 
        from 
            inscription i
        left join utilisateur u on u.id = i.utilisateur_id
        left join profil_utilisateur p1 on p1.id = u.profil_id
        left join profil_utilisateur p2 on p2.id = p1.parent_id
        where 
            i.creneau_id = cpu.creneau_id 
            and 
            i.statut not in ('annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative')
            and
            (
                p1.id = cpu.profil_utilisateur_id or p2.id = cpu.profil_utilisateur_id
            )
    )
;

update
    reservabilite_profil_utilisateur rpu
set
    rpu.nb_inscrits = (
        select 
            count(*) 
        from 
            inscription i
        left join utilisateur u on u.id = i.utilisateur_id
        left join profil_utilisateur p1 on p1.id = u.profil_id
        left join profil_utilisateur p2 on p2.id = p1.parent_id
        where 
            i.reservabilite_id = rpu.reservabilite_id 
            and 
            i.statut not in ('annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative')
            and
            (
                p1.id = rpu.profil_utilisateur_id or p2.id = rpu.profil_utilisateur_id
            )
    )
;

update
    format_activite_profil_utilisateur fapu
set
    fapu.nb_inscrits = (
        select 
            count(*) 
        from 
            inscription i
        left join utilisateur u on u.id = i.utilisateur_id
        left join profil_utilisateur p1 on p1.id = u.profil_id
        left join profil_utilisateur p2 on p2.id = p1.parent_id
        where 
            i.format_activite_id = fapu.format_activite_id 
            and 
            i.creneau_id is null
            and 
            i.statut not in ('annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative')
            and
            (
                p1.id = fapu.profil_utilisateur_id or p2.id = fapu.profil_utilisateur_id
            )
    )
;
