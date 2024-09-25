<template>
    <div class="wp2sv-clock" v-if="ready">
        <p class="time-utc">
            {{i18n.__('Your server time in UTC is:','wordpress-2-step-verification')}} <span>{{server_time}}</span>
            <a @click="syncTime" id="sync-clock" class="sync-link" :class="loading?'loading':''"
               :title="i18n.__('Sync time','wordpress-2-step-verification')">{{i18n.__('Sync time','wordpress-2-step-verification')}}</a>
        </p>
        <p class="time-local" v-if="serverTime!==localTime">
            {{sprintf(i18n.__('Your local time in %s is:','wordpress-2-step-verification'),timezone)}} <span>{{local_time}}</span>
        </p>
    </div>
</template>
<script>
    import i18n from "../libs/i18n";
    export default {
        props:['serverTime','localTime','timezone'],
        mixins: [i18n],
        data:function(){
            return {
                tickerId: null,
                server:0,
                local:0,
                server_time:'',
                local_time:'',
                loading:0,
                ready:0,
            }
        },
        created:function(){
            this.getTime(this.serverTime,this.localTime);
            this.ticker();
            this.tickerId=setInterval(this.ticker, 1000);
            this.ready=1;
            this.syncTime();
        },
        destroyed:function(){
            if(this.tickerId)
                clearInterval(this.tickerId);
        },
        methods: {
            ticker:function() {
                var time=new Date().getTime();
                this.server_time = this.timeString(time+this.server);
                this.local_time = this.timeString(time+this.local);
            },
            syncTime:function(){
                this.loading=1;
                var self=this;
                wp2sv.post('time_sync').then(function(r){
                    self.loading=0;
                    self.getTime(r.data.server,r.data.local);
                    return r;
                });
            },
            getTime: function (server,local) {
                if (server) {
                    server = server * 1000;
                    this.server = server - new Date().getTime();
                }
                if (local) {
                    local = local * 1000;
                    this.local = local - new Date().getTime();
                }
            },
            timeString: function (UNIX_timestamp) {
                var a = new Date(UNIX_timestamp);
                var year = a.getUTCFullYear();
                var month = a.getUTCMonth() + 1;
                var date = a.getUTCDate();
                var hour = a.getUTCHours();
                var min = a.getUTCMinutes();
                var sec = a.getUTCSeconds();
                var zeroise = function (i) {
                    if (i < 10) {
                        i = "0" + i
                    }  // add zero in front of numbers < 10
                    return i;
                };
                hour = zeroise(hour);
                month = zeroise(month);
                date = zeroise(date);
                min = zeroise(min);
                sec = zeroise(sec);
                return ( year + '-' + month + '-' + date + ' ' + hour + ':' + min + ':' + sec );
            }
        }
    }
</script>
