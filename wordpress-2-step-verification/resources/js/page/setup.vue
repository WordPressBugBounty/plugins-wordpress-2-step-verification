<template>
    <div>
        <wp2sv-status v-bind="{
                enabled:state.enabled,
                enabled_at:state.enabled_at,
                time:state.time,

            }"></wp2sv-status>
        <div v-if="!state.enabled" class="wp2sv-container wp2sv-start">
            <wp2sv-start></wp2sv-start>
        </div>
        <div class="wp2sv-container" v-else>
            <wp2sv-settings></wp2sv-settings>
        </div>


        <div id="wp2sv-change-device" class="wp2sv-modal" tabindex="0">
            <authenticator></authenticator>
        </div>
        <div id="wp2sv-add-email" class="wp2sv-modal" tabindex="0">
            <wp2sv-emails></wp2sv-emails>
        </div>
        <div id="wp2sv-backup-codes-modal" class="wp2sv-modal" tabindex="0">
            <backup-codes></backup-codes>
        </div>
        <div id="wp2sv-confirm" class="wp2sv-modal">
            <div class="wp2sv-modal-content">
                <div class="wp2sv-h1"></div>
                <div class="wp2sv-p"></div>
                <div class="wp2sv-row">
                    <div class="pull-right wp2sv-actions">
                        <span class="wp2sv-action wp2sv-confirm-btn wp2sv-cancel-btn wp2sva-black">{{i18n.__('Cancel','wordpress-2-step-verification')}}</span>
                        <span class="wp2sv-action wp2sv-confirm-btn" data-btn-ok>{{i18n.__('Ok','wordpress-2-step-verification')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import i18n from "../libs/i18n";

    import store from '../libs/store';
    export default {
        mixins: [i18n],
        data: function () {
            return {
                state:store.state
            }
        },
        mounted: function () {

        },
        methods: {

        },
        computed: {}
    }
</script>
