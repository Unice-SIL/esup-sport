var changeStatut = function(idEl, statut){ 
    if(statut == 0){
        let el = $('.js-inscription[data-id="'+idEl+'"]');
        $(el.parent()).html($("#js-text-inscrit-clone")[0].innerHTML);
        
    }
}

global.changeStatut = changeStatut;