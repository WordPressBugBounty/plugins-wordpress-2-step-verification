<template id="wp2sv-app-passwords">
    <div>
        <h2 class="wp-heading-inline">
            <a href="#" @click="backToSetup" class="wp2sv-back">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </a>
            {{i18n.__('App passwords', 'wordpress-2-step-verification')}}</h2>
        <p>{{i18n.__('App passwords let you sign in to your Wordpress Account from apps on devices that don\'t support 2-Step Verification. You\'ll only need to enter it once so you don\'t need to remember it.', 'wordpress-2-step-verification')}}</p>
        <div class="wp2sv-app-passwords">
            <div class="wp2sv-container">

                <table id="the-app-passwords" class="wp2sv-table" v-if="passwords.length">
                    <thead>
                    <tr class="row">
                        <th class="col-name">{{i18n.__('Name', 'wordpress-2-step-verification')}}</th>
                        <th class="col-created">{{i18n.__('Created', 'wordpress-2-step-verification')}}</th>
                        <th class="col-last-used">{{i18n.__('Last used', 'wordpress-2-step-verification')}}</th>
                        <th class="col-access">{{i18n.__('Access', 'wordpress-2-step-verification')}}</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr v-for="(p,i) in passwords" class="app-password-item row">
                        <td class="col-name">{{ p.n }}</td>
                        <td class="col-created"
                            data-c="">{{ p.c }}
                        </td>
                        <td class="col-last-used">{{ p.u ? p.u : '&ndash;' }}</td>
                        <td class="col-access">
                            <button class="wp2sv-btn" @click="remove(i)">
                                <span class="dashicons dashicons-trash wp2sv-clickable"></span>
                                {{i18n.__('Revoke', 'wordpress-2-step-verification')}}
                            </button>
                        </td>
                    </tr>

                    </tbody>
                </table>
                <div v-else
                     class="no-app-pass">{{i18n.__('You have no app passwords.', 'wordpress-2-step-verification')}}</div>
                <div class="app-add-password">
                <span>
                    <input v-model="name" type="text" maxlength="100" class="app-name"
                           placeholder="e.g. WP on my Android">
                </span>

                    <button class="wp2sv-btn wp2sv-btn-primary" :disabled="!name"
                            @click="generate">{{i18n.__('Generate', 'wordpress-2-step-verification')}}</button>

                </div>
            </div>
        </div>

        <div id="app-password-created" class="wp2sv-modal" tabindex="0">
            <div class="wp2sv-card">
                <div class="wp2sv-h1">
                    {{i18n.__('Generated app password','wordpress-2-step-verification')}}
                </div>
                <div class="card-body">
                    <div
                        class="apc-title">{{i18n.__('Your app password for your device', 'wordpress-2-step-verification')}}</div>
                    <div class="apc-pass"></div>
                    <div class="apc-direction">
                        <div class="apc-title">{{i18n.__('How to use it', 'wordpress-2-step-verification')}}</div>

                        <p>{{i18n.__('Go to the settings for your Wordpress Account in the application or device you are trying to set up. Replace your password with the 16-character password shown above.', 'wordpress-2-step-verification')}}
                            <br>
                            {{i18n.__('Just like your normal password, this app password grants complete access to your Wordpress Account. You won\'t need to remember it, so don\'t write it down or share it with anyone.', 'wordpress-2-step-verification')}}
                        </p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="wp2sv-row">
                        <div class="pull-right">
                        <span
                            class="wp2sv-action wp2sv-modal-close">{{i18n.__('Done', 'wordpress-2-step-verification')}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import i18n from '../libs/i18n';
import $ from '../libs/jquery';
import store from '../libs/store';

export default {
    props: ["app_passwords"],
    mixins: [i18n],
    data: function () {
        return {
            passwords: store.state.app_passwords || [],
            name: ""
        }
    },
    methods: {
        backToSetup(e){
            e.preventDefault();
            store.set('page','index');
        },
        generate: function () {
            var self = this;
            wp2sv.toast.info(this.i18n.__('Working...', 'wordpress-2-step-verification'));
            wp2sv.post('password_create', {name: this.name}).done(function (result) {
                if (result.data) {
                    self.passwords.push(result.data);
                    self.showPassword(result.data.p);
                }

                wp2sv.toast.hide();
            });


        },
        remove: function (index) {
            var self = this;
            var i = this.passwords[index].i;
            wp2sv.toast.info(this.i18n.__('Working...', 'wordpress-2-step-verification'));
            wp2sv.post('password_remove', {index: i}).done(function (result) {
                if (result.success) {
                    self.passwords.splice(index, 1);
                }

                wp2sv.toast.hide();
            });

        },
        showPassword: function (pass) {
            var $modal = $('#app-password-created');
            var res = '';
            if (typeof pass === "string") {
                var chunk = pass.match(/.{1,4}/g);
                chunk.forEach(function (p) {
                    res += '<span class="apc-pchunk"><span>' + p.split('').join('</span><span>') + '</span></span>';
                });
            }
            $modal.find('.apc-pass').html(res);
            wp2sv.openModal($modal);
        }
    },
    mounted: function () {
        console.log(this.passwords);
    }
}
</script>
