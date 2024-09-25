/* global ajaxurl, wp2sv_url*/

window.wp2sv=window.wp2sv||{};
(function(wp2sv,$){
    "use strict";
    var openedModal;
    var opening=0;
    var zIndex=9999;
    wp2sv.init=function(){
        $(document).on('click','[data-wp2sv-modal]',function(e){
            e.preventDefault();
            wp2sv.openModal($(this).data('wp2sv-modal'));
        });
        $(document).on('click','.wp2sv-modal-close',function(e){
            e.preventDefault();
            wp2sv.closeModal(this);
        })
    };
    wp2sv.openModal=function(modal){
        modal=$(modal).closest('.wp2sv-modal');
        if(!modal.find('.wp2sv-modal-content').length){
            modal.wrapInner('<div class="wp2sv-modal-content"></div>');
            //console.log(modal);
        }
        modal.trigger('open');
        $(document).trigger('wp2sv-modal-open');
        if(!modal.find('.wp2sv-modal-close-icon').length){
            modal.append('<div class="wp2sv-modal-close"><span class="wp2sv-modal-close-icon"></span></div>')
        }
        opening++;
        modal.wrap('<div class="wp2sv-modal-mask"><div class="wp2sv-modal-wrap"></div></div>');
        modal.closest('.wp2sv-modal-mask').css('z-index',zIndex+opening);

        openedModal=modal;
    };
    wp2sv.closeModal=function(modal){
        modal=modal||openedModal;
        modal=$(modal);
        modal=modal.closest('.wp2sv-modal');
        modal.trigger('close');
        $(document).trigger('wp2sv-modal-close');
        modal.unwrap().unwrap();
        modal.trigger('closed');
        opening--;
    };
    var confirm_index=1;
    wp2sv.confirm=function(title,message,callback,is_alert){
        var $confirm=$('#wp2sv-confirm');
        $confirm.after($confirm=$confirm.clone());
        $confirm.attr('id','wp2sv-confirm-'+confirm_index++).addClass('wp2sv-confirm-modal');
        if(typeof message==='function'){
            callback=message;
            message=title;
            title='';
        }
        if(typeof callback!=='function'){
            callback=function(){
                return true;
            }
        }
        title&&$('.wp2sv-h1',$confirm).html(title);
        message&&$('.wp2sv-p',$confirm).html(message);
        if(is_alert){
            $('.wp2sv-cancel-btn',$confirm).addClass('hidden');
        }
        $confirm.on('closed',function(){
            $confirm.remove();
        });
        let promise= new Promise(function (resolve, reject) {
            $confirm.on('click', '.wp2sv-confirm-btn', function () {
                var result;
                if ($(this).is('[data-btn-ok]')) {
                    result=resolve(true);
                } else {
                    result=resolve(false);
                }
                if(result!==false) {
                    wp2sv.closeModal($confirm);
                }
            });
        });
        if(callback){
            promise.then(callback);
        }
        wp2sv.openModal($confirm);
        return promise;
    };
    wp2sv.alert=function(message,title,callback){
        if(typeof title==='function'){
            callback=title;
            title='';
        }
        wp2sv.confirm(title,message,callback,true);
    };
    wp2sv.post=function(action,data){
        return wp2sv.ajax(action,data,'POST');
    };
    wp2sv.get=function(action,data){
        return wp2sv.ajax(action,data);
    };
    wp2sv.ajax=function(action,data,method){
        if(typeof action==='object'){
            if(!method) {
                method = data;
            }
            data=action;
        }else {
            if (typeof data === 'object') {
                data.action = action;
            }else{
                data={action:action};
            }
        }
        method=method||'GET';
        data=data||{};
        if(data.action){
            data.wp2sv_action=data.action;
            data.wp2sv_nonce=wp2sv._nonce;
        }
        data.action='wp2sv';
        return $.ajax({
            type: method,
            dataType: "json",
            url: wp2sv.ajaxurl,
            data:data
        });
    };
    wp2sv.print=function(e){
        var data=$(e).html();
        var mywindow = window.open('', 'backup codes', 'height=400,width=600');
        mywindow.document.write('<html><head><title></title>');
        /*optional stylesheet*/
        mywindow.document.write('<link rel="stylesheet" href="'+parent.wp2sv.url.assets+'css/print.css'+'" type="text/css" />');
        mywindow.document.write('</head><body ><div class="page"><div class="subpage">');
        mywindow.document.write(data);
        mywindow.document.write('</div></div></body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10
        mywindow.onload=function(){
            //mywindow.print();
            //mywindow.close();
        };
    };
    wp2sv.toast=function(text,style){
        return this.show(text,style);
    };
    Object.assign(wp2sv.toast,{
        show:function(text,style){
            if(typeof style==='undefined'){
                style='info';
            }
            this.el().find('.wp2sv-toast-message').html(text);
            var classes=style?'wp2sv-toast wp2sv-toast-' + style:'wp2sv-toast';
            this.el().find('>div').attr('class', classes);
            this.el().show();
            return this;
        },
        info:function(message){
            return this.show(message,'info');
        },
        error:function(message){
            return this.show(message,'error');
        },
        success:function(message){
            return this.show(message,'success');
        },
        warning:function(message){
            return this.show(message,'warning');
        },
        hide:function(){
            this.el().hide();
            return this;
        },
        el:function(){
            if(!this.$el){
                this.$el=$('#wp2sv-toast');
            }
            if(!this.$el.length){
                let template='<div id="wp2sv-toast" style="display: none">\n' +
                    '        <div class="wp2sv-toast">\n' +
                    '            <div class="wp2sv-toast-message">\n' +
                    '\n' +
                    '            </div>\n' +
                    '        </div>\n' +
                    '    </div>';
                this.$el=$(template).appendTo('.wp2sv');

            }
            return this.$el;
        },
        hideAfter:function (miliseconds) {
            setTimeout(this.hide,miliseconds);
            return this;
        }
    });
    wp2sv.toast.hide=wp2sv.toast.hide.bind(wp2sv.toast);
    wp2sv.init();

})(window.wp2sv,jQuery);