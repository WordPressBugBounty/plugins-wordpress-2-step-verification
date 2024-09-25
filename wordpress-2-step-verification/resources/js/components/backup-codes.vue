<template id="wp2sv-backup-codes">
    <div class="card-content">
        <div class="card-head">
            <div class="wp2sv-h1">{{i18n.__('Save your backup codes','wordpress-2-step-verification')}}</div>
            <p>{{i18n.__('Keep these backup codes somewhere safe but accessible.','wordpress-2-step-verification')}}</p>
        </div>
        <div class="card-body">

            <div v-if="backup_codes" class="backup-codes wp2sv-text-center">
                <table class="backup-codes-list">
                    <tr v-for="row in backup_codes">
                        <td v-for="col in row">
                            <span class="cb" v-if="!col.used"></span>
                            <span>{{col.used ? i18n.__('ALREADY USED','wordpress-2-step-verification') : col.code}}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="wp2sv-loading" v-else>
                <div class="wp2svi-loading"><svg class="icircular" viewBox="25 25 50 50">
                    <circle class="ipath" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                </svg></div>
                <p>{{i18n.__('Loading backup codes...','wordpress-2-step-verification')}}</p>
            </div>
            <div v-if="backup_codes" class="backup-codes">
                <div class="backup-info">
                    <div><img :src="icon128"></div>
                    <p>{{site_url}} ({{user_login}})</p>
                </div>
                <ul class="backup-note">
                    <li>{{i18n.__('You can only use each backup code once.','wordpress-2-step-verification')}}</li>
                    <li class="show-if-print" v-html="sprintf(i18n.__('Need more? Visit %s','wordpress-2-step-verification'),sprintf('<a href=&quot;%s&quot;>%s</a>',site_url,site_url_without_http))"></li>
                    <li>{{i18n.__('These codes were generated on:','wordpress-2-step-verification')}} {{date}}</li>
                </ul>
            </div>

            <p class="wp2sv-text-center hide-if-print btn-new-codes">
                <button class="wp2sv-btn" @click="generate">{{i18n.__('Get new codes','wordpress-2-step-verification')}}</button>
            </p>
        </div>
        <div class="card-footer">
            <div class="wp2sv-row">
                <div class="pull-right">
                    <span class="wp2sv-action wp2sv-modal-close">{{i18n.__('Close','wordpress-2-step-verification')}}</span>
                    <span class="wp2sv-action" @click="download">{{i18n.__('Download','wordpress-2-step-verification')}}</span>
                    <span class="wp2sv-action" @click="print">{{i18n.__('Print','wordpress-2-step-verification')}}</span>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import $ from '../libs/jquery';
import i18n from '../libs/i18n';
import store from '../libs/store';

export default {
        data:function(){
            return {
                backup_codes:false,
                date:'',
                icon128:wp2sv.url.assets+'/images/icon-128x128.png',
                user_login:store.state.user_login,
                site_url:wp2sv.url.site,
            }
        },
        mixins:[i18n],
        mounted:function(){
            this.loadCode();
        },
        computed:{
            site_url_without_http(){
                return this.site_url.replace('http://','').replace('https://','');
            }
        },
        methods:{
            download:function(){
                window.location.href=wp2sv.ajaxurl + '?action=wp2sv&wp2sv_action=download_backup_codes&wp2sv_nonce=' + wp2sv._nonce;
            },
            loadCode:function(){
                var self=this;
                $(this.$el).closest('.wp2sv-modal').on('open',function(){
                    self.getCodes();
                })
            },
            getCodes:function(generate){
                var self=this;
                self.backup_codes=false;
                wp2sv.get('backup-codes',{'generate':generate?1:0}).then(function(res){
                    if(res && res.success && res.data && res.data.codes){
                        return res.data;
                    }
                }).then(function(backup){
                    if(backup) {
                        store.set('backup_codes',backup.unused);
                        self.backup_codes = backup.codes;
                        self.date=backup.date;
                    }
                })
            },


            print:function(){
                document.body.scrollTop = 0; // For Chrome, Safari and Opera
                document.documentElement.scrollTop = 0; // For IE and Firefox
                window.print();
            },
            generate:function(){
                var self=this;
                wp2sv.confirm(this.i18n.__('Get new codes?','wordpress-2-step-verification'),this.i18n.__('If you get a new set of backup codes, none of your current codes will work.','wordpress-2-step-verification'),function(yes){
                    if(yes){
                        self.getCodes(true);
                    }
                })
            }

        }
    }
</script>
