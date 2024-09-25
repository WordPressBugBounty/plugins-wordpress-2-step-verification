<template>
    <div v-cloak class="v-cloak">
        <wp2sv-setup v-if="state.page==='index'"></wp2sv-setup>
        <wp2sv-app-passwords v-if="state.page==='app_passwords'"></wp2sv-app-passwords>
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
