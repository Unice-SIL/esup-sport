scheduler.data = {
    item: ITEM
};

$.post(DATAAPI, {
    lists: {
        ressources: {
            class: '\\App\\Entity\\Uca\\Ressource'
        },
        encadrant: {
            class: '\\App\\Entity\\Uca\\Utilisateur',
            findBy: {
                repository: "findtByGroupsName",
                param: "Encadrant"
            }
        },
        tarifs: {
            class: '\\App\\Entity\\Uca\\Tarif'
        },
        activites: {
            class: '\\App\\Entity\\Uca\\Activite'
        },
        periodesFermeture: {
            class: '\\App\\Entity\\Uca\\PeriodeFermeture'
        },
    }

}, function(data) {
    scheduler.data.lists = data;

    for (const periode of scheduler.data.lists.periodesFermeture) {
        periode.dateDeb = new Date(periode.dateDeb);
        periode.dateFin = new Date(periode.dateFin);
    }

    scheduler.data.lists.profils = [];
    scheduler.data.lists.capaciteProfil = []
    scheduler.data.lists.niveauxSportifs = [];
    scheduler.data.lists.encadrant = [];

    if (ITEM.profilsUtilisateurs != null) {
        scheduler.data.lists.profils = ITEM.profilsUtilisateurs;
    }
    if (ITEM.niveauxSportifs != null) {
        niveauxSportifs = ITEM.niveauxSportifs;
        for (const niveauSportif of niveauxSportifs) {
            scheduler.data.lists.niveauxSportifs.push(niveauSportif.niveauSportif);
        }
    }
    if (ITEM.encadrants != null) {
        scheduler.data.lists.encadrant = ITEM.encadrants;
    }
    if (ITEM.lieu != null) {
        scheduler.data.lists.lieu = ITEM.lieu;
    }


    if (scheduler.data.item.type == "creneau") {
        var nouveauCreneau = ['description', 'informations', 'tarif', 'profils'];
        var creneauExistant = ['description', 'informations', 'tarif', 'capacite', 'profils'];
        scheduler.data.lists.profils.forEach(function(formatProfil) {
            let labelCapacite = 'capaciteProfil_' + formatProfil.profilUtilisateur.id;;
            nouveauCreneau.push(labelCapacite);
            creneauExistant.push(labelCapacite);
        });
        scheduler.config.lightbox.toDisplay = {
            new: nouveauCreneau.concat(['capacite', 'niveauSportif', 'encadrant', 'lieu', 'recurring', 'eligibilite', 'frequence', 'time']),
            update: creneauExistant.concat(['niveauSportif', 'encadrant', 'lieu', 'eligibilite', 'frequence', 'time'])
        };
    } else if (scheduler.data.item.type == "reservation") {
        scheduler.config.lightbox.toDisplay = {
            new: ['description', 'resources', 'recurring', 'time'],
            update: ['description', 'resources', 'time']
        };
    } else if (scheduler.data.item.type == "ressource") {
        let nouvelleRessource = ['description', 'time', 'capacite', 'profils'];
        let ressourceExistante = ['description', 'time', 'capacite', 'profils'];
        scheduler.data.lists.profils.forEach(function(formatProfil) {
            let labelCapacite = 'capaciteProfil_' + formatProfil.profilUtilisateur.id;;
            nouvelleRessource.push(labelCapacite);
            ressourceExistante.push(labelCapacite);
        });
        scheduler.config.lightbox.toDisplay = {
            new: nouvelleRessource,
            update: ressourceExistante
        };
    }


    //not display element if  they not exist
    for (var i in scheduler.config.lightbox.toDisplay) {
        scheduler.config.lightbox.toDisplay[i].filter(function(value, index, arr) {
            if (value == "encadrant" && !scheduler.data.item.estEncadre) {
                delete arr[index];
            }
            if (value == "profils" && scheduler.data.lists.profils.length == 0) {
                delete arr[index];
            }
            if (value == "niveauSportif" && scheduler.data.lists.niveauxSportifs.length == 0) {
                delete arr[index];
            }
        });
    }

    if (typeof(ITEM.dateFinEffective) !== "undefined") {
        var date = new Date(ITEM.dateFinEffective);
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        if (day < 10) {
            day = '0' + day;
        }
        if (month < 10) {
            month = '0' + month;
        }
        scheduler.config.repeat_date_of_end = [day, month, year].join('-');
    }

}).fail(_uca.ajax.fail);

scheduler.data.fn = {};
scheduler.data.fn.toOptions = function(list) {
    if (list.data == null) {
        return;
    }

    var listeOptions = [];
    if (list.firstValueEmpty && list.data.length > 1) {
        listeOptions = [{ key: '', label: Translator.trans('common.select.emptyValue') }];
    }

    if (typeof list.libelleSeparateur == 'undefined') {
        list.libelleSeparateur = " ";
    }

    return listeOptions.concat(
        list.data.map(function(item) {
            var label = "";
            for (let i = 0; i < list['libelle'].length; i++) {
                const element = list['libelle'][i];
                if (label != "") {
                    label += list.libelleSeparateur;
                }

                if (item.objectClass == 'App\\Entity\\Uca\\FormatActiviteProfilUtilisateur' || item.objectClass == 'App\\Entity\\Uca\\RessourceProfilUtilisateur') {
                    item = item.profilUtilisateur;
                }
                label += item[element];
            }
            label = label.trim();
            return { key: item[list['id']], label: label };
        })
    );
}