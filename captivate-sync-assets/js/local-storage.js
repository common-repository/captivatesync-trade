(function($){
    $.fn.cfmLocalStorage = function(options){
        'use strict';

        if(typeof Storage !== "undefined"){

            var form     = $(this),
                key      = cfmsync.CFMH_SHOWID + '_' + $(this).attr('id')+'_save_storage',
                defaults = {
                    exclude_type: [],
                    exclude_name: [],
                    interval: 60000
                };

            var opts = $.extend({}, defaults, options);

            var excludeInputType = function(){
                var inputType = '';

                $.each(opts.exclude_type, function(k,v){
                    inputType += 'input[type='+v+'],'
                });

                return inputType;
            };

            var excludeInputName = function(){
                var inputName = '';

                $.each(opts.exclude_name, function(k,v){
                    inputName += 'input[name='+v+'],'
                });

                return inputName;
            };

            form.find(':input').bind('change keyup', function () {
                var serializeForm = form.serializeArray();
                localStorage.setItem(key, JSON.stringify(serializeForm));
            });

            setInterval(function () {
                var serializeForm = form.serializeArray();
                localStorage.setItem(key, JSON.stringify(serializeForm));
            }, opts.interval);

            var cfmInitApp = function(){
                if(localStorage.getItem(key) !== null){

                    var data          = JSON.parse(localStorage.getItem(key)),
                        inputRadio    = form.find('input[type=radio]'),
                        inputCheckbox = form.find('input[type=checkbox]');

                    for(var i = 0; i < data.length; i++){
                        form.find(':input[name="'+data[i].name+'"]')
                            .not(excludeInputType() + excludeInputName() + 'input[type=radio], input[type=checkbox]').val(data[i].value);

                        for(var j = 0; j < inputRadio.length; j++){
                            if(inputRadio[j].getAttribute('name') === data[i].name && inputRadio[j].getAttribute('value') === data[i].value){
                                inputRadio[j].checked = true;
                            }
                        }

                        for(var k = 0; k < inputCheckbox.length; k++){
                            if(inputCheckbox[k].getAttribute('name') === data[i].name && inputCheckbox[k].getAttribute('value') === data[i].value){
                                inputCheckbox[k].checked = true;
                            }
                        }
                    }
                }
            };

            /*form.submit(function () {
                // LOCALSTORAGE - cleared all on publish-episode.js
            });*/

            cfmInitApp();
        }
        else {
            console.error('Sorry! No web storage support.')
        }
    };

    $.fn.cfmGetLocalStorage = function(form_id, input_name) {
        if(typeof Storage !== "undefined"){

            var key = cfmsync.CFMH_SHOWID + '_' + form_id+'_save_storage';

            if(localStorage.getItem(key) !== null){

                local_data = JSON.parse(localStorage.getItem(key));

                for(var i = 0; i < local_data.length; i++) {
                    if ( local_data[i].name == input_name ) {
                        return local_data[i].value;
                    }
                }
            }
        }
    }


})(jQuery);
