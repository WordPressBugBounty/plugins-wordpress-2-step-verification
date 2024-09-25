import $ from '../libs/jquery';
if(!window.wp2sv.store) {
    const { __, _x, _n, _nx } = wp.i18n;
    window.wp2sv.store = {
        state: {
            page:'index',
            active_sessions:0,
            app_passwords:[],
            backup_codes:0,
            emails:[],
            mobile_dev:'',
            mobile_at:'',
            enabled:false,
            enabled_at:'',
            otp:{},
            time:{
                local:'',
                server:'',
                timezone:'',
            },
            user_display_name:'',
            user_login:'',
            _nonce:'',
        },
        update(state) {
            Object.assign(this.state, state);
        },
        set(key, value) {
            this.state[key] = value;
        },
        load(){
            let store=this;
            wp2sv.toast.info(__('Loading...','wordpress-2-step-verification'));
            return $.ajax({
                type: 'POST',
                dataType: "json",
                url: wp2sv.ajaxurl,
                data: {action: 'wp2sv_setup_data'}
            }).done(function (data) {
                if (data) {
                    store.update(data);
                    wp2sv.toast.hide();
                } else {
                    store.reload();
                }
            }).fail(function () {
                store.reload();
            });
        },
        reload: function () {
            window.location.reload();
        },

    };
    window.wp2sv.store.update(window.wp2sv_setup)
}
export default window.wp2sv.store;
