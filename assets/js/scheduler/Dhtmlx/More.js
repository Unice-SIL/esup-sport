import {ACL} from "./ACL";

var More = {

        //instance all action to open the form
        init: function(){
            this.eventLightBowCustomEmail();
            this.event();


            //redefine the sidebar event buttons
            scheduler.config.icons_select.push("icon_more");
                
            
            // set the label of the new button
            scheduler.locale.labels.icon_more = "More";

        },

        eventLightBowCustomEmail: function(){       
            this.custom_form = document.getElementById("my_form");

        },

        event: function(){
            var obj = this;
            //handle the event here
            scheduler._click.buttons.more = function(id){
                obj.id = id;
                
                ACL.action("more");

                window.location = PATH_SEE_MORE+id;
            };
        },


};

export {More}