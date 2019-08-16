var ACL = {

    utilisateur: null,

    init: function(){
        this.role = {
            admin: [
                "ALL"
            ],
            encadrant: [
                    "mail",
                    "ev_onclick",
                    "delete",
            ]
        } 
    },


    action: function(event){
        //admin mode user can do everything
        if (this.role[this.utilisateur][0] == "ALL") {
            return true;
        }

        //here we have a user that is not admin we check what he can do
        for (let i = 0; i < this.role[this.utilisateur].length; i++) {
            const element = this.role[this.utilisateur][i];
            if(element == event){
                return true;
            }     
        }

        //we don't find the event in the user role
        return false;
    },

    

};

export {ACL}