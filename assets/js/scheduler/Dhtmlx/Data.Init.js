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
        }
    }
}, function (data) {
    scheduler.data.lists = data;

    scheduler.data.lists.profils = [];
    scheduler.data.lists.niveauxSportifs = [];

    if (ITEM.profilsUtilisateurs != null){
        scheduler.data.lists.profils = ITEM.profilsUtilisateurs;
    }
    if (ITEM.niveauxSportifs != null){
        scheduler.data.lists.niveauxSportifs = ITEM.niveauxSportifs;
    }

    if(scheduler.data.item.type == "creneau"){
        scheduler.config.lightbox.toDisplay = {
            new: ['description', 'tarif', 'profils',  'capacite', 'niveauSportif','encadrant', 'recurring', 'time'],
            update:['description', 'tarif',  'capacite', 'profils', 'niveauSportif', 'encadrant', 'time']
        };
    }
    else if(scheduler.data.item.type == "reservation"){
        scheduler.config.lightbox.toDisplay = {
            new: ['description', 'resources',  'recurring', 'time'],
            update: ['description', 'resources', 'time']
        };
    }
    else if(scheduler.data.item.type == "ressource"){
        scheduler.config.lightbox.toDisplay = {
            new: ['description', 'recurring', 'time'],
            update: ['description', 'time']
        };
    }

    //not display element if  they not exist
    for(var i  in scheduler.config.lightbox.toDisplay){
        scheduler.config.lightbox.toDisplay[i].filter(function(value, index, arr){
            if(value == "encadrant" && !scheduler.data.item.estEncadre){
                delete arr[index];
            }
            if(value == "profils" && scheduler.data.lists.profils.length == 0){
                delete arr[index];
            }
            if(value == "niveauSportif" && scheduler.data.lists.niveauSportif.length == 0){
                console.log("test");
                delete arr[index];
            }
        });
    }
});

scheduler.data.fn = {};
scheduler.data.fn.toOptions = function (list) {
    return list.data.map(function(item){
        var label = "";
        for (let i = 0; i < list['libelle'].length; i++) {
            const element = list['libelle'][i];
            label += " "+item[element];  
        }
        label = label.trim();
        return {key: item[list['id']], label: label};
    });
}
