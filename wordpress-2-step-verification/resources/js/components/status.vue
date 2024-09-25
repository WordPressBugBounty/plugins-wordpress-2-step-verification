<template>
    <div id="wp2sv-status" class="wp2sv-row wp2sv-notice" :class="enabled?'wp2sv-notice-success':'wp2sv-notice-error'">
        <div class="wp2sv-col">
            <p v-if="enabled" v-html="sprintf(i18n.__('2-step verification is <strong>ON</strong> since %s', 'wordpress-2-step-verification'),enabled_at)"></p>
            <p v-else v-html="i18n.__('2-step verification is <strong>OFF</strong>', 'wordpress-2-step-verification')"></p>
            <p>

                <a v-if="enabled" href="#" id="wp2sv-disable-link" @click="disable">{{i18n.__('Turn off 2-step verification...', 'wordpress-2-step-verification')}}</a>

                <a v-else href="#" @click="enable">{{i18n.__('Turn on 2-step verification...', 'wordpress-2-step-verification')}}</a>

            </p>
        </div>
        <div class="wp2sv-col">
            <wp2sv-clock v-bind:timezone="time.timezone"
                         v-bind:server-time="time.server"
                         v-bind:local-time="time.local">
            </wp2sv-clock>
        </div>
    </div>
</template>
<script>
    import i18n from "../libs/i18n";
    import store from "../libs/store";

    export default {
        props:['enabled','enabled_at','time'],
        mixins: [i18n],
        methods:{
            enable: function (e) {
                e && e.preventDefault();
                this.$root.$emit('enroll:start')
            },
            disable: function (e) {
                e && e.preventDefault();
                let self = this;
                wp2sv.confirm(this.i18n.__('Turn off 2-Step Verification?','wordpress-2-step-verification'),
                    this.i18n.__('Turning off 2-Step Verification will remove the extra security on your account, and you’ll only use your password to sign in.','wordpress-2-step-verification')
                ).then(function (yes) {
                    if (yes) {
                        wp2sv.post('disable').done(function () {
                            store.set('enabled',false);
                        });
                    }
                });

            },
        }
    }
</script>
