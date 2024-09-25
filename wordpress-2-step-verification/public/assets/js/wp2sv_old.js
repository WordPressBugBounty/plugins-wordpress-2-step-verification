/* global ajaxurl, wp2sv_url*/
jQuery('#wp2sv-config-section').ready(function($){




    var wp2sv_action_el=$('#wp2sv_action');





    
    
});
var wp2sv=wp2sv||{};
(function(wp2sv,$){
    "use strict";
    var openedModal;
   // var vars={};
    var modalBG=$('.modal-dialog-bg');
    wp2sv.init=function(){
        wp2sv.vars={};
        wp2sv.setVars();
        wp2sv.setUpClock();
        wp2sv.updateTime();
    };
    wp2sv.setVars=function(){
        wp2sv.vars.serverClock=$('#wp2sv-server-time');
        wp2sv.vars.localClock=$('#wp2sv-local-time');
    };
    wp2sv.openModal=function(modal){

        var ww=$(window).width();
        var wpContent=$('#wpcontent');

        var contentLeft=wpContent.offset().left+wpContent.outerWidth()-wpContent.width();

        var wwm=ww-contentLeft*2;
        var wh=$(window).height()+2*$(window).scrollTop();
        modal=$(modal);
        var mw=modal.outerWidth();
        var mh=modal.outerHeight();
        modalBG.css('width',ww).css('height',wh).show();
        modal.css('top',(wh-mh)/2);
        modal.css('left',(wwm-mw)/2);
        modal.fadeTo(500,1,function(){
            modal.show();
        });
        openedModal=modal;
    };
    wp2sv.closeModal=function(modal){
        modalBG.hide();
        modal=modal||openedModal;
        modal=$(modal);
        modal=modal.closest('.modal-dialog');
        modal.fadeTo(500,0,function(){
            modal.hide();
        });
    };
    wp2sv.updateTime=function(link){
        if(typeof link != 'undefined')
            link.addClass('loading');
        $.ajax({
            type: "POST",
            dataType:"json",
            url: ajaxurl,
            data: { 'action': "wp2sv", 'wp2sv_action':'time_sync' },
            success: function(data){
                if(data.server_time){
                    wp2sv.vars.serverClock.data('timestamp',data.server_time).attr('data-timestamp',data.server_time);
                    wp2sv.vars.localClock.data('timestamp',data.local_time).attr('data-timestamp',data.local_time);
                    wp2sv.vars.serverClock.data('time-diff','').attr('data-time-diff','');
                    wp2sv.vars.localClock.data('time-diff','').attr('data-time-diff','');
                    if(typeof link != 'undefined')
                        link.removeClass('loading');
                }
            }

        });
    };
    wp2sv.setUpClock=function(){

        setInterval(function(){
            wp2sv.setClock(wp2sv.vars.serverClock);
            wp2sv.setClock(wp2sv.vars.localClock);
        },100);
    };
    wp2sv.setClock=function (e){
        e=$(e);
        var server=e.data('timestamp');
        if(!server){
            //console.log('no sever time');
            return false;
        }
        server=server*1000;
        var diff= e.data('time-diff');
        var local=new Date().getTime();
        if(!diff){
            diff=server-local;
            e.data('time-diff',diff);
        }
        diff=parseFloat(diff);
        var new_server=diff+local;
        //console.log('server time'+new_server);
        var timeString=wp2sv.timeConverter(new_server);
        if(timeString!= e.html()) {
            e.html(timeString);
        }
    };


    wp2sv.timeConverter=function(UNIX_timestamp){
        var a = new Date(UNIX_timestamp);
        var year = a.getUTCFullYear();
        var month = a.getUTCMonth()+1;
        var date = a.getUTCDate();
        var hour = a.getUTCHours();
        var min = a.getUTCMinutes();
        var sec = a.getUTCSeconds();
        month=this.checkTime(month);
        date=this.checkTime(date);
        min=this.checkTime(min);
        sec=this.checkTime(sec);
        return ( year + '-' + month + '-' + date + ' ' + hour + ':' + min + ':' + sec );

    };
    wp2sv.checkTime=function(i) {
        if (i<10) {i = "0" + i}  // add zero in front of numbers < 10
        return i;
    };
    wp2sv.print=function(e){
        var data=$(e).html();
        var mywindow = window.open('', 'backup codes', 'height=400,width=600');
        mywindow.document.write('<html><head><title></title>');
        /*optional stylesheet*/
        mywindow.document.write('<link rel="stylesheet" href="'+parent.wp2sv_url+'/print.css'+'" type="text/css" />');
        mywindow.document.write('</head><body >');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10

        mywindow.print();
        mywindow.close();
    };
    wp2sv.init();



})(wp2sv,jQuery);
wp2sv.configPage={};
(function(config,$,root){
    var self=config;
    config.init=function(){
        this.setSelectors();
        this.setVars();
        this.setEvents();
    };
    config.setSelectors=function(){
        this.$el=$('#wp2sv-config-section');
        this.$container=this.$el.find('#config-container');
        this.$selectDevice=$('#device-type');
        this.$deviceSelection=$('#device-type-selection');
        this.$inactive=$('#inactive-elements');
        this.$emailConfig=$('#email-address');
        this.$appConfig=$('#configure-app');
        this.$appInstructions=$('#configure-app-instructions');
        this.$currentDevice=$('#wp2sv_page_config');
        this.$subHeader=$('#pm-subheader');
        this.$nextButton=$("#next-button");
        this.$backButton=$("#back-button");
        this.$form=$('#wp2sv-config-form');
        this.$action=$('#wp2sv_action');
    };
    config.setVars=function(){

    };
    config.setEvents=function(){
        if(this.getDevice()){
            this.$deviceSelection.hide();
            this.$subHeader.hide();
        }
        this.$selectDevice.on('change',function(){
            var device=$(this).val();
            self.selectDevice(device);
        }).change();
        this.setupEmail();
        this.setupNextBack();
        this.setupApp();
        this.setupModal();
        this.setupOverview();
    };
    config.setDevice=function(device){
        this.$currentDevice.val(device);
    };
    config.selectDevice=function(device){
        this.resetContainer();
        this.setDevice(device);
        switch(device){
            case 'email':
                this.$emailConfig.appendTo(this.$container);
                break;
            case 'android': case 'blackberry': case 'iphone':
                this.$appConfig.appendTo(this.$container);
                this.$appInstructions.find('>div').appendTo(this.$inactive);
                var instruction='#configure-app-'+device;
                $(instruction).appendTo(this.$appInstructions);
            break;


            default:
                this.resetContainer();
                break;
        }
        $.ajax({
            type: "POST",
            dataType:"json",
            url: ajaxurl,
            data: { 'action': "wp2sv", 'device-type': device, 'wp2sv_action':'device_type_choice' },
            success: function(data){

            }

        });
    };
    config.resetContainer=function(){
        this.$el.find('.config-section').not(this.$deviceSelection).appendTo(this.$inactive);
    };

    config.setupEmail=function(){
        var $email=$('#primary-email');
        $email.change(function(){
            var email=$(this).val();
            var primary_phone_valid=$("#primary-phone-valid");
            primary_phone_valid.css('visibility','hidden');
            $("#primary-send-code").attr('disabled','');
            if(email){
                primary_phone_valid.attr('src',wp2sv_url+'/images/loading.gif');
                primary_phone_valid.css('visibility','inherit');
                $("#primary-error").addClass('inactive');
                $.ajax({
                    type: "POST",
                    dataType:"json",
                    url: ajaxurl,
                    data: { 'action': "wp2sv", 'email': email, 'wp2sv_action':'check' },
                    success: function(data){
                        if(data.result=='success'){
                            $("#primary-phone-valid").attr('src',wp2sv_url+'/images/checkmark-g16.png');
                            $("#primary-send-code").removeAttr('disabled');

                        }else{
                            $("#primary-phone-valid").css('visibility','hidden');
                            $("#primary-error").html(data.message).removeClass('inactive');
                        }
                    }

                });
            }
        });
        $email.change().keyup(function(){
            Wp2svTyping.interceptKeypress();
        });
        $('#primary-send-code').on('click',function(){
            var send_code_icon=$(".send-code-container .icon");
            send_code_icon.attr('src',wp2sv_url+'/images/loading.gif');
            send_code_icon.css('visibility','inherit');
            $("#primary-code-sent").html("");
            $(this).attr('disabled','');
            $.ajax({
                type: "POST",
                dataType:"json",
                url: ajaxurl,
                data: { 'action': "wp2sv", 'email': $email.val(), 'wp2sv_action':'send_mail' },
                success: function(data){
                    if(data==null)
                        return false;
                    if(data.result=='success'){
                        send_code_icon.attr('src',wp2sv_url+'/images/checkmark-g16.png');
                        send_code_icon.css('visibility','inherit');
                        var input=$("#primary-test-input");
                        input.removeClass('inactive-text');
                        input.find('input').removeAttr('disabled');
                    }else{
                        send_code_icon.css('visibility','hidden');
                    }

                    $("#primary-code-sent").html(data.message);
                    $('#primary-send-code').removeAttr('disabled');
                    $('#primary-verify-button').focus();
                    $('#primary-verify-code').focus();
                }

            });

            return false;
        });
        $('#primary-verify-button').on('click',function(){
            var primary_icon=$("#primary-test-input").find(".icon");
            primary_icon.attr('src',wp2sv_url+'/images/loading.gif');
            primary_icon.css('visibility','inherit');
            var is_Changing=$(this).attr('name')=='VerifyAndSaveApp';
            var email=is_Changing?$('#primary-email').val():'';
            $.ajax({
                type: "POST",
                dataType:"json",
                url: ajaxurl,
                data: { 'action': "wp2sv", 'wp2sv_code': $('#primary-verify-code').val(), 'wp2sv_action':'verify_code', 'is_email':'1',wp2sv_email:email },
                success: function(data){
                    if(data==null)
                        return false;
                    if(data.result=='success'){
                        if(is_Changing){
                            window.location.href=wp2svI10n.home;return false;
                        }
                        primary_icon.attr('src',wp2sv_url+'/images/checkmark-g16.png');
                        $("#primary-verify-container").html($('#email-verify-success'));
                        $("#next-button").removeAttr('disabled');

                    }else{
                        primary_icon.attr('src',wp2sv_url+'/images/warning-y16.png');
                        $("#primary-verify-container").html(data.message);
                    }


                }


            });

            return false;
        });
    };
    config.setupNextBack=function(){
        this.$nextButton.on('click',function(){
            var current=$("#process-map").find(".pm-current").attr('id');
            $(".config-section").appendTo(self.$inactive);

            if(current=='pm-generate'){
                $("#pm-generate").removeClass().addClass('pm-complete');
                $("#pm-remember").removeClass().addClass('pm-current');
                $("#back-button").removeAttr('disabled');
                self.$subHeader.hide();
                $("#remember-computer-state").appendTo(self.$container);
            }
            if(current=='pm-remember'){
                $("#pm-remember").removeClass().addClass('pm-complete');
                $('#pm-confirm').removeClass().addClass('pm-current');
                $(this).addClass('hidden');
                $('#activate-button').show();
                $('#confirm-section').appendTo(self.$container);
            }
        });
        this.$backButton.on('click',function(){
            var current=$("#process-map").find(".pm-current").attr('id');
            $(".config-section").appendTo(self.$inactive);
            if(current=='pm-confirm'){
                $("#pm-confirm").removeClass().addClass('pm-incomplete');
                $("#pm-remember").removeClass().addClass('pm-current');
                $("#next-button").removeClass('hidden');
                $('#activate-button').hide();
                $("#remember-computer-state").appendTo('#config-container');
            }
            if(current=='pm-remember'){
                $("#pm-remember").removeClass().addClass('pm-incomplete');
                $('#pm-generate').removeClass().addClass('pm-current');
                $(this).attr('disabled','');
                self.$subHeader.show();
                self.$deviceSelection.appendTo('#config-container');
                $('#device-type').change();

            }
        });
    };
    config.setupApp=function(){
        var app_verify_failed_attemp=0;
        $("#app-verify-button").on('click',function(){
            var code=$('#app-verify-code').val();
            var configure_icon=$("#configure-app").find(".smallicon");
            var app_verify_container=$("#app-verify-container");
            configure_icon.attr('src',wp2sv_url+'/images/loading.gif');
            configure_icon.css('visibility','inherit');
            var secret=$('#wp2sv_secret').val();
            var is_Changing=$(this).attr('name')=='VerifyAndSaveApp';
            $.ajax({
                type: "POST",
                dataType:"json",
                url: ajaxurl,
                data: { 'action': "wp2sv", 'wp2sv_code': code, 'wp2sv_action':'verify_code',secret:secret, device:self.getDevice()},
                success: function(data){
                    if(data==null)
                        return false;

                    if(data.result=='success'){
                        if(is_Changing){
                            window.location.href=wp2svI10n.home;return false;
                        }
                        $("#app-verify-failures").appendTo(this.$inactive);
                        configure_icon.attr('src',wp2sv_url+'/images/checkmark-g16.png');
                        app_verify_container.html($('#app-verify-success'));
                        $("#next-button").removeAttr('disabled');

                    }else{
                        app_verify_failed_attemp++;
                        configure_icon.attr('src',wp2sv_url+'/images/warning-y16.png');
                        app_verify_container.html(data.message);
                        if(app_verify_failed_attemp>=3){
                            $("#app-verify-failures").appendTo(app_verify_container);
                        }
                    }


                }


            });
            return false;
        });


        $(".manual-zippy a").toggle(function(){
                $(this).closest('.manual-zippy').find('.app-instructions').removeClass('inactive');
                $(".manual-zippy img.icon").attr('src',wp2sv_url+'/images/zippy_minus_sm.gif');
            },function(){
                $(this).closest('.manual-zippy').find('.app-instructions').addClass('inactive');
                $(".manual-zippy img.icon").attr('src',wp2sv_url+'/images/zippy_plus_sm.gif');
            }
        );
    };
    config.setupModal=function(){
        $('.modal-open').on('click',function(e){
            e.preventDefault();
            var modalToOpen=$(this).data('modal');
            modalToOpen='#'+modalToOpen;
            wp2sv.openModal(modalToOpen);
            return false;
        });
        $('.modal-dialog-title-close').on('click',function(e){
            e.preventDefault();
            wp2sv.closeModal(this);
            return false;
        });
        $('.modal-dialog-buttons button[name=cancel]').on('click',function(e){
            e.preventDefault();
            wp2sv.closeModal(this);
            return false;
        });
    };
    config.setupOverview=function(){
        $('#activate-button').on('click',function(e){
            e.preventDefault();
            self.submitAction('enable');
            return false;
        });
        $('#cancel-link').on('click',function(){
            window.location.href=wp2svI10n.home;
            return false;
        });



        $('#phone-change').on('click','.wp2sv-buttonset-action',function(e){
            if($('[name=settings-choose-app-type-radio]:checked').length<=0){
                e.preventDefault();
                $('#settings-no-choice-app-error').show();
                return false;
            }
            self.setAction('change_mobile');
        });
        $('[name=settings-choose-app-type-radio]').on('click',function(){
            $('#settings-no-choice-app-error').hide();
        });
        $("#remove-email-link").on('click',function(){
            var confirm_message=wp2svI10n['remove_confirm'];
            var result=true;
            if(confirm_message){
                result=confirm(confirm_message);
            }
            if(!result)
                return false;
            self.submitAction('remove_email');
            return false;
        });
        $("#wp2sv-enable-link").on('click',function(){
            self.goTo('auto');
            return false;
        });
        $('#wp2sv-disable').on('click','.wp2sv-buttonset-action',function(e){
            e.preventDefault();
            self.submitAction('disable');

        });

        this.$el.find('.add-device').on('click',function(e){
            e.preventDefault();
            var device=$(this).data('device');
            self.goTo(device);
        });
        $("#edit-email-link").on('click',function(){
            self.setDevice('email');
            self.submitAction('change_email');
            return false;
        });

        $("#sync-clock").on('click',function(e){
            e.preventDefault();
            wp2sv.updateTime($(this));
            return false;
        });
        $("#trust_computer").on('click',function(){
            self.submitAction('set_remember');
            return false;
        });
        $("#show-codes-link").on('click',function(){
            $(this).hide();
            $("#printable-codes").slideDown();
            return false;
        });
        $('#generate-codes-button').on('click',function(e){
            e.preventDefault();
            self.submitAction('generate-backup-codes');
        });
        $('#download-backup-codes').on('click',function(e){
            e.preventDefault();
            self.submitAction('download-backup-codes');
        });
        $('#print-backup-codes').on('click',function(){
            wp2sv.print('#backup-codes-for-print');
        });
    };
    config.getDevice=function(){
        return this.$currentDevice.val();
    };

    config.goTo=function(device){
        this.setDevice(device);
        this.$form.submit();
    };
    config.submitAction=function(action){
        this.setAction(action);
        this.$form.submit();
    };
    config.setAction=function(a){
        this.$action.val(a);
    };

    config.init();

})(wp2sv.configPage,jQuery,wp2sv);
wp2sv.appPasswords={};
(function(app,$){
    app.init=function(){
        app.setSelectors();
        app.setVars();
        app.setEvents();
        app.checkTable();
    };
    app.setVars=function(){

    };
    app.setSelectors=function(){
        var $app=app.$app=$('.wp2sv-app-passwords');
        app.$appName=$('input.app-name',$app);
        app.$generate=$('.generate',$app);
        app.$listTable=$('#the-app-passwords',$app);
        app.$popup=$('#app-password-created',$app);
        app.$password=$('.apc-pass span.iZ',app.$popup);
        app.$nopass=$('.no-app-pass',$app);
    };
    app.setEvents=function(){
        app.$appName.on('keyup',function(){
            if($(this).val()){
                app.$generate.addClass('enabled');
            }else{
                app.$generate.removeClass('enabled');
            }
        });
        app.$generate.on('click',function(e){
            e.preventDefault();
           if(app.$generate.hasClass('enabled')){
               app.generatePassword();
           }
        });
        app.$listTable.on('click','.revoke-btn',function(){
            var id=$(this).data('i');
            var row=$(this).closest('.row');
            $.ajax({
                type: "POST",
                dataType:"json",
                url: ajaxurl,
                data: { 'action': "wp2sv", 'wp2sv_action':'revoke_password',i:id},
                success: function(data){
                    row.remove();
                    app.checkTable();
                }

            });
        });
    };
    app.checkTable=function(){
        if(app.$listTable.find('tbody tr').length){
            app.$listTable.show();
            app.$nopass.hide();
        }else{
            app.$listTable.hide();
            app.$nopass.show();
        }
    };
    app.generatePassword=function(){
        $.ajax({
            type: "POST",
            dataType:"json",
            url: ajaxurl,
            data: { 'action': "wp2sv", 'wp2sv_action':'generate_password',app_name:app.$appName.val()},
            success: function(data){
                if(data) {
                    app.showPassword(data.p);
                    setTimeout(function(){app.addPasswordToList(data)},500);
                }
            }

        });
    };
    app.addPasswordToList=function(p){
        var row='';
        if(!p.u){
            p.u='&ndash;';
        }
        row+='<tr class="app-password-item row">';
        row+='<td class="col-name">'+p.n+'</td>';
        row+='<td data-t="'+p.c+'" class="col-created">'+p.c+'</td>';
        row+='<td class="col-last-used" data-t="'+p.u+'">'+p.u+'</td>';
        row+='<td class="col-access">';
        row+='<div class="revoke-btn" data-i="'+p.i+'">';
        row+=wp2svI10n['revoke'];
        row+='</div>';
        row+='</td>';
        row+='</tr>';
        app.$listTable.find('tbody').append(row);
        app.checkTable();
    };
    app.showPassword=function(pass){
        var res='';
        if(typeof pass=="string"){
            var chunk=pass.match(/.{1,4}/g);
            chunk.forEach(function(p){
                res+='<span class="jZ"><span>' + p.split('').join('</span><span>') + '</span></span>';
            });
        }
        app.$password.html(res);
        wp2sv.openModal(app.$popup);
    };
    app.init();

})(wp2sv.appPasswords,jQuery);

//wp2sv.openModal('.modal-dialog');
Wp2svTyping = {

   interval : 750,

   lastKeypress : null,

   interceptKeypress : function() {
      this.lastKeypress = new Date().getTime();
      var that = this;
      setTimeout(function() {
         var currentTime = new Date().getTime();
         if(currentTime - that.lastKeypress > that.interval) {
            that.sendRequest();
         }
      }, that.interval + 100);
   },

   sendRequest : function() {
      jQuery('#primary-email').change();
   }

};