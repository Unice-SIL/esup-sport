var Load = {
    count: 0,
    loader: $("#load"),

    start: function(){
        this.loader.show();
    },

    stop: function(){
        this.loader.hide();
    },

}

export {Load}