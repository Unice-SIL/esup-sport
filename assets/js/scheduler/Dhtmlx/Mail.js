import {ACL} from "./ACL";

var Mail = {

        //instance all action to open the form
        init: function(){
            this.eventLightBowCustomEmail();
            this.event();


            //redefine the sidebar event buttons
            scheduler.config.icons_select.push("icon_email");
                
            
            // set the label of the new button
            scheduler.locale.labels.icon_email = "Email";

        },

        eventLightBowCustomEmail: function(){       
            this.custom_form = document.getElementById("my_form");

        },

        //needs to be attached to the 'save' button
        save_form: function () {
            var ev = scheduler.getEvent(scheduler.getState().lightbox_id);

            let text = $(".textarea-mail").val();
            
            this.sendMail(this.id, text);

            //ev.text = document.getElementById("some_input").value;
            scheduler.endLightbox(true, this.custom_form);
        },
        
        //needs to be attached to the 'cancel' button
        close_form: function (argument) {
            scheduler.endLightbox(false, this.custom_form);
        },


        sendMail: function(id, text){
                var id_el  =id;
                $.ajax({
                    method: "POST",
                    url: "{{ path('DhtmlxSendMail') }}",
                    data: {
                        id: id_el,
                        text: text
                    }
                }).done(function (data) {

                }).fail(_uca.ajax.fail);
        },

        event: function(){
            var obj = this;
            //handle the event here
            scheduler._click.buttons.email = function(id){
                obj.id = id;
                
                ACL.action("mail");

                obj.show();
            };



            $("#btn-mail-send").click(function(){
                obj.save_form();
            });

            $("#btn-mail-back").click(function(){
                scheduler.endLightbox(true, obj.custom_form);
            });
        },
  
        show: function(id){
            var ev = scheduler.getEvent(id);
            scheduler.startLightbox(this.id, this.custom_form);
        }

};

export {Mail}