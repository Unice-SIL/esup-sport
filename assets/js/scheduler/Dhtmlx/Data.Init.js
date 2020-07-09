scheduler.data = {
    item: ITEM
};

$.post(DATAAPI, {
    lists: {
        ressources: {
            class: '\\UcaBundle\\Entity\\Ressource'
        },
        encadrant: {
            class: '\\UcaBundle\\Entity\\Utilisateur',
            findBy: {
                repository: "findtByGroupsName",
                param: "Encadrant"
            }
        },
        tarifs: {
            class: '\\UcaBundle\\Entity\\Tarif'
        },
        activites: {
            class: '\\UcaBundle\\Entity\\Activite'
        },
    }

}, function (data) {
    scheduler.data.lists = data;
    
    scheduler.data.lists.profils = [];
    scheduler.data.lists.capaciteProfil = []
    scheduler.data.lists.niveauxSportifs = [];
    scheduler.data.lists.encadrant = [];

    if (ITEM.profilsUtilisateurs != null) {
        scheduler.data.lists.profils = ITEM.profilsUtilisateurs;
    }
    if (ITEM.niveauxSportifs != null) {
        scheduler.data.lists.niveauxSportifs = ITEM.niveauxSportifs;
    }
    if (ITEM.encadrants != null) {
        scheduler.data.lists.encadrant = ITEM.encadrants;
    }
    if (ITEM.lieu != null) {
        scheduler.data.lists.lieu = ITEM.lieu;
    }

    
    if (scheduler.data.item.type == "creneau") {
        var nouveauCreneau = ['description', 'tarif', 'profils'];
        var creneauExistant = ['description', 'tarif', 'capacite', 'profils'];
        scheduler.data.lists.profils.forEach(function(formatProfil) {
            let labelCapacite = 'capaciteProfil_' + formatProfil.profilUtilisateur.id;;
            nouveauCreneau.push(labelCapacite);
            creneauExistant.push(labelCapacite);
        });
        scheduler.config.lightbox.toDisplay = {
            new: nouveauCreneau.concat(['capacite', 'niveauSportif', 'encadrant', 'lieu', 'recurring', 'eligibilite', 'time']),
            update:  creneauExistant.concat(['niveauSportif', 'encadrant', 'lieu', 'eligibilite', 'time'])
        };
    }

    else if (scheduler.data.item.type == "reservation") {
        scheduler.config.lightbox.toDisplay = {
            new: ['description', 'resources', 'recurring', 'time'],
            update: ['description', 'resources', 'time']
        };
    }
    else if (scheduler.data.item.type == "ressource") {
        scheduler.config.lightbox.toDisplay = {
            new: ['description', 'recurring', 'time'],
            update: ['description', 'time']
        };
    }


    //not display element if  they not exist
    for (var i in scheduler.config.lightbox.toDisplay) {
        scheduler.config.lightbox.toDisplay[i].filter(function (value, index, arr) {
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

    if (typeof (ITEM.dateFinEffective) !== "undefined") {
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
        scheduler.config.repeat_date_of_end = [day, month, year].join('/');
    }

}).fail(_uca.ajax.fail);

scheduler.data.fn = {};
scheduler.data.fn.toOptions = function (list) {
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
        list.data.map(function (item) {
            var label = "";
            for (let i = 0; i < list['libelle'].length; i++) {
                const element = list['libelle'][i];
                if (label != "") {
                    label += list.libelleSeparateur;
                }

                if (item.objectClass == 'UcaBundle\\Entity\\FormatActiviteProfilUtilisateur') {
                    item = item.profilUtilisateur;
                }
                label += item[element];
            }
            label = label.trim();
            return { key: item[list['id']], label: label };
        })
    );
}
