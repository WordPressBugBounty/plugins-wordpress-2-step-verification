<template>
    <component v-bind:is="stepComponent"></component>
</template>
<script>
    export default {
        data:function(){
            return {
                step:"welcome"
            }
        },
        created:function(){
            this.$root.$on('enroll:cancel',this.cancel);
            this.$root.$on('enroll:start',this.start);
            this.$root.$on('enroll:email-flow',this.emailEnroll);
            this.$root.$on('enroll:app-flow',this.appEnroll)
        },
        methods:{
            start:function(e){
                e&&e.preventDefault();
                this.emailEnroll();
            },
            appEnroll:function(e){
                this.step='app';
            },
            emailEnroll:function(){
                console.log('email');
                this.step='email';
            },
            cancel:function(){
                this.step='welcome';
            }
        },
        computed:{
            stepComponent:function(){
                return 'wp2sv-enroll-'+this.step;
            }
        }
    }
</script>
