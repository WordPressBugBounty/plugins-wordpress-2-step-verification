<template>
    <div>
    <h2>{{i18n.__('Your second step','wordpress-2-step-verification')}}</h2>
    <p>{{i18n.__('After entering your password, you’ll be asked for a second verification step.','wordpress-2-step-verification')}}</p>

    <div class="wp2sv-card" v-if="state.mobile_dev">
        <div class="wp2sv-row">
            <div class="card-icon">
                <div class="wp2sv-icon wp2svi-ga"></div>
            </div>
            <div class="card-content wp2sv-col">
                <div class="card-head">
                    <div class="wp2sv-h1">
                        {{i18n.__('Authenticator app','wordpress-2-step-verification')}} <strong class="">({{i18n.__('Default','wordpress-2-step-verification')}})</strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="wp2sv-row">
                        <div class="wp2sv-col">
                            <div class="wp2sv-h2">{{sprintf(i18n.__('Authenticator on %s','wordpress-2-step-verification'),state.mobile_dev)}}</div>
                        </div>
                        <div class="wp2sv-col-0"><span @click="removeApp" class="dashicons dashicons-trash wp2sv-clickable"></span></div>
                    </div>
                    <div class="wp2sv-text" v-if="state.mobile_added">{{i18n.__('Added:','wordpress-2-step-verification')}} {{state.mobile_added}}</div>
                </div>
                <div class="card-footer">
                    <span class="wp2sv-action" data-wp2sv-modal="#wp2sv-change-device">{{i18n.__('Change phone','wordpress-2-step-verification')}}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="wp2sv-card" v-if="state.emails.length">
        <div class="wp2sv-row">
            <div class="card-icon">
                <div class="wp2sv-icon wp2svi-message"></div>
            </div>
            <div class="card-content wp2sv-col">
                <div class="card-head">
                    <div class="wp2sv-h1">
                        {{i18n.__('Text message','wordpress-2-step-verification')}} <strong v-if="!state.mobile_dev">({{i18n.__('Default','wordpress-2-step-verification')}})</strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="wp2sv-email" v-for="(email, index) in state.emails">
                        <div class="wp2sv-row">
                            <div class="wp2sv-col">
                                <div class="wp2sv-h2">{{email.e}} <small v-if="index===0" class="wp2sv-text-primary">({{i18n.__('Primary','wordpress-2-step-verification')}})</small></div>
                            </div>
                            <div class="wp2sv-col-0">
                                <span v-if="index>0" title="Set as primary" class="dashicons dashicons-sticky wp2sv-clickable" @click="primaryMail(index)"></span>
                                <span title="Remove" class="dashicons dashicons-trash wp2sv-clickable" @click="removeEmail(index)"></span>
                            </div>
                        </div>
                        <div class="wp2sv-text" v-if="email.t" :title="email.t">{{i18n.__('Added:','wordpress-2-step-verification')}} {{email.added}}</div>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="wp2sv-action" data-wp2sv-modal="#wp2sv-add-email">{{i18n.__('Add email','wordpress-2-step-verification')}}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="wp2sv-card" v-if="state.backup_codes">
        <div class="wp2sv-row">
            <div class="card-icon">
                <div class="wp2sv-icon wp2svi-airplane"></div>
            </div>
            <div class="card-content wp2sv-col">
                <div class="card-head">
                    <div class="wp2sv-h1">
                        {{i18n.__('Backup codes','wordpress-2-step-verification')}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="wp2sv-text">{{sprintf(i18n.__('%s single-use codes are active at this time, but you can generate more as needed.','wordpress-2-step-verification'),state.backup_codes)}}</div>
                </div>
                <div class="card-footer">
                    <span class="wp2sv-action" data-wp2sv-modal="#wp2sv-backup-codes-modal">{{i18n.__('Show codes','wordpress-2-step-verification')}}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="wp2sv-alternative-section" v-if="!state.mobile_dev || state.emails.length<1 || !state.backup_codes">
        <h2>{{i18n.__('Set up alternative second step','wordpress-2-step-verification')}}</h2>
        <p>{{i18n.__("Set up at least one backup option so that you can sign in even if your other second steps aren’t available.",'wordpress-2-step-verification')}}</p>
        <div class="wp2sv-card" v-if="!state.mobile_dev">
            <div class="wp2sv-row">
                <div class="card-icon">
                    <div class="wp2sv-icon wp2svi-ga"></div>
                </div>
                <div class="card-content wp2sv-col">
                    <div class="card-head">
                        <div class="wp2sv-h1">
                            {{i18n.__('Authenticator app','wordpress-2-step-verification')}}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="wp2sv-text">{{i18n.__('Use the Authenticator app to get free verification codes, even when your phone is offline. Available for Android and iPhone.','wordpress-2-step-verification')}}</div>
                    </div>
                    <div class="card-footer">
                        <span class="wp2sv-action" data-wp2sv-modal="#wp2sv-change-device">{{i18n.__('Set up','wordpress-2-step-verification')}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="wp2sv-card" v-if="state.emails.length < 1">
            <div class="wp2sv-row">
                <div class="card-icon">
                    <div class="wp2sv-icon wp2svi-message"></div>
                </div>
                <div class="card-content wp2sv-col">
                    <div class="card-head">
                        <div class="wp2sv-h1">
                            {{i18n.__('Backup email','wordpress-2-step-verification')}}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="wp2sv-text">{{i18n.__('Add a backup phone so you can still sign in if you lose your phone.','wordpress-2-step-verification')}}</div>
                    </div>
                    <div class="card-footer">
                        <span class="wp2sv-action" data-wp2sv-modal="#wp2sv-add-email">{{i18n.__('Add email')}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="wp2sv-card" v-if="!state.backup_codes">
            <div class="wp2sv-row">
                <div class="card-icon">
                    <div class="wp2sv-icon wp2svi-airplane"></div>
                </div>
                <div class="card-content wp2sv-col">
                    <div class="card-head">
                        <div class="wp2sv-h1">
                            {{i18n.__('Backup codes','wordpress-2-step-verification')}}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="wp2sv-text">{{i18n.__('These printable one-time passcodes allow you to sign in when away from your phone, like when you’re traveling.','wordpress-2-step-verification')}}</div>
                    </div>
                    <div class="card-footer">
                        <span class="wp2sv-action" data-wp2sv-modal="#wp2sv-backup-codes-modal">{{i18n.__('Set up','wordpress-2-step-verification')}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="wp2sv-card" v-if="false">
            <div class="wp2sv-row">
                <div class="card-icon">
                    <div class="wp2sv-icon wp2svi-usb"></div>
                </div>
                <div class="card-content wp2sv-col">
                    <div class="card-head">
                        <div class="wp2sv-h1">
                            {{i18n.__('Security Key','wordpress-2-step-verification')}}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="wp2sv-text">{{i18n.__('A Security Key is a small physical device used for signing in. It plugs into your computer\'s USB port.','wordpress-2-step-verification')}}
                            <a href="">Learn more</a>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="wp2sv-action" data-wp2sv-modal="#wp2sv-backup-codes-modal">{{i18n.__('Add Security Key','wordpress-2-step-verification')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h2>{{i18n.__('App passwords','wordpress-2-step-verification')}}</h2>
    <p>{{i18n.__('App passwords let you sign in to your Wordpress Account from apps on devices that don\'t support 2-Step Verification','wordpress-2-step-verification')}}</p>

    <div class="wp2sv-card">
        <div class="wp2sv-row">
            <div class="card-icon">
                <div class="wp2sv-icon wp2svi-app-passwords"></div>
            </div>
            <div class="card-content wp2sv-col">
                <div class="card-head">
                    <div class="wp2sv-h1">
                        <a class="wp2sv-action" @click="appPasswords">{{ sprintf(i18n._n('%d password','%d passwords',state.app_passwords.length ,'wordpress-2-step-verification'),state.app_passwords.length)}}</a>
                    </div>
                </div>
                <div class="card-footer">

                </div>
            </div>
        </div>
    </div>

    <h2>{{i18n.__('Devices that do not need a second step.','wordpress-2-step-verification')}}</h2>
    <p>{{i18n.__('You can skip the second step on devices you trust, such as your own computer.','wordpress-2-step-verification')}}</p>

    <div class="wp2sv-card">
        <div class="wp2sv-row">
            <div class="card-icon">
                <div class="wp2sv-icon wp2svi-devices"></div>
            </div>
            <div class="card-content wp2sv-col">
                <div class="card-head">
                    <div class="wp2sv-h1">
                        {{i18n.__('Devices you trust','wordpress-2-step-verification')}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="wp2sv-text">
                        {{i18n.__('Revoke trusted status from your devices that skip 2-Step Verification.','wordpress-2-step-verification')}}
                        <br>{{sprintf(_n('There is %s active session','There are %s active sessions',state.active_sessions,'wordpress-2-step-verification'),state.active_sessions)}}
                    </div>
                </div>
                <div class="card-footer">
                    <span class="wp2sv-action" @click="revokeTrusted">{{i18n.__('Revoke all','wordpress-2-step-verification')}}</span>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>
<script>
import i18n from "../libs/i18n";
import store from "../libs/store";
export default {
    mixins: [i18n],
    data: function () {
        return {state:store.state};
    },
    methods:{
        appPasswords:function (){
            store.set('page','app_passwords');
        },
        removeApp: function () {
            var self = this;
            if (this.state.emails.length < 1) {
                return wp2sv.alert(this.i18n.__('2-Step Verification isn\'t allowed without an email or the Authenticator app'))
            }
            let confirm=this.sprintf(this.i18n.__('Removing this option will make verification codes on %s your default second step. Are you sure you want to proceed?'),
                this.state.emails[0].e
            );
            wp2sv.confirm('', confirm).then(function (yes) {
                if (yes) {
                    wp2sv.post({
                        action: 'remove-app'
                    }).done(function (result) {
                        if (!result) {
                            return;
                        }
                        if (result.success) {
                            self.state.mobile_dev = '';
                        } else {

                        }
                    }).always(function () {
                    });
                }
            });
        },
        removeEmail: function (id) {
            let state=this.state;
            let email = state.emails[id];
            if (!state.mobile_dev && state.emails.length === 1) {
                return wp2sv.alert(this.i18n.__('2-Step Verification isn\'t allowed without an email or the Authenticator app'))
            }
            wp2sv.toast.info(this.i18n.__('Working...','wordpress-2-step-verification'));
            wp2sv.post({
                action: 'remove-email',
                email: email.id
            }).done(function (result) {
                if (!result) {
                    return;
                }
                if (result.success) {
                    state.emails.splice(id, 1);
                } else {

                }
            }).always(function () {
                wp2sv.toast.hide();
            });

        },
        primaryMail: function (index) {
            let state=this.state;
            let email = state.emails[index]
            wp2sv.toast.info(this.i18n.__('Working...','wordpress-2-step-verification'));
            wp2sv.post({
                action: 'primary-email',
                email: email.id
            }).done(function (result) {
                if (!result) {
                    return;
                }
                if (result.success) {
                    state.emails.splice(index, 1);
                    state.emails.unshift(email);
                } else {

                }
            }).always(function () {
                wp2sv.toast.hide();
            });
        },
        revokeTrusted:function(){
            var self=this;
            wp2sv.confirm(this.i18n.__('Revoke trust from all devices','wordpress-2-step-verification'),
                this.i18n.__('To sign in again you\'ll need your phone or a backup option for the second step.','wordpress-2-step-verification')).then(function(confirmed){
                if(confirmed) {
                    wp2sv.post({
                        action: 'destroy_other_sessions'
                    }).done(function (result) {
                        wp2sv.toast.info(self.i18n.__('Trusted device reset','wordpress-2-step-verification')).hideAfter(2000);
                    })

                }
            });
        },
    }
}
</script>
