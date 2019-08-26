import {ACL} from "./ACL";

var Registered = {

        //instance all action to open the form
        init: function(){
            this.eventLightBoxCustom();
            this.event();


            //redefine the sidebar event buttons
            scheduler.config.icons_select.push("icon_register");
                
            
            // set the label of the new button
            scheduler.locale.labels.icon_register = "Registered";

        },

        eventLightBoxCustom: function(){       
            this.custom_form = document.getElementById("register_form");

            
        },

        //needs to be attached to the 'save' button
        save_form: function () {
            var ev = scheduler.getEvent(scheduler.getState().lightbox_id);

            

            //ev.text = document.getElementById("some_input").value;
            scheduler.endLightbox(true, this.custom_form);
        },
        
        //needs to be attached to the 'cancel' button
        close_form: function (argument) {
            scheduler.endLightbox(false, this.custom_form);
        },

        event: function(){
            var obj = this;
            //handle the event here
            scheduler._click.buttons.register = function(id){
                obj.id = id;
                
                ACL.action("register");

                obj.show(id);
            };



            $("#btn-register-send").click(function(){
                obj.save_form();
            });

            $("#btn-register-back").click(function(){
                scheduler.endLightbox(true, obj.custom_form);
            });
        },
  
        show: function(id){
            var ev = scheduler.getEvent(id);
            let parent = scheduler._events[id].getParent();
            this.populate(parent.creneau.inscriptions);
            scheduler.startLightbox(this.id, this.custom_form);
        },

        populate(data){
            let html = "";
            $($(this.custom_form).find("ul")[0])[0].innerHTML = "";
            if(typeof data != "undefined"){
                for (let i = 0; i < data.length; i++) {
                    const element = data[i];
                    html+='<li><span>'+element.utilisateur.nom+'</span><input class="check_person" type="checkbox"></li>'
                    
                }
            }
            else{
                html = Translator.trans("inscription.aucun.inscrit");

            }


            $($(this.custom_form).find("ul")[0]).append(html);

        }

};

export {Registered}