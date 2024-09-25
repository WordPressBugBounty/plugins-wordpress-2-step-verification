import Route from './page/route';
import Setup from './page/setup';
import AppPass from './page/app-passwords';
import Status from './components/status';
import Settings from './components/settings';
import Clock from './components/clock';
import EnrollEmail from './components/enroll-email';
import EnrollApp from './components/enroll-app';
import EnrollWelcome from './components/enroll-welcome';
import Start from './components/start';
import Authenticator from "./components/authenticator";
import BackupCodes from "./components/backup-codes";
import Emails from "./components/emails";
import Vue from 'vue';
(function(factory){
    // Establish the root object, `window` (`self`) in the browser, or `global` on the server.
    // We use `self` instead of `window` for `WebWorker` support.
    var root = (typeof self === 'object' && self.self === self && self) ||
        (typeof global === 'object' && global.global === global && global);
    root.wp2sv=root.wp2sv||{};
    root.wp2sv.setup=factory(Vue,_,jQuery);

})
(function(Vue,_,$){

    let module={
        init:function(){
            this.registerComponents();
            if($('#wp2sv-setup').length) {
                this.vm = new Vue({
                    el: '#wp2sv-setup',
                });
            }
        },
        registerComponents:function(){
            Vue.component('wp2sv-route',Route);
            Vue.component('wp2sv-setup',Setup);
            Vue.component('wp2sv-app-passwords',AppPass);
            Vue.component('wp2sv-status',Status);
            Vue.component('wp2sv-settings',Settings);
            Vue.component('wp2sv-clock',Clock);
            Vue.component('wp2sv-enroll-email',EnrollEmail);
            Vue.component('wp2sv-enroll-app',EnrollApp);
            Vue.component('wp2sv-enroll-welcome',EnrollWelcome);
            Vue.component('wp2sv-start',Start);

            Vue.component('authenticator', Authenticator);

            Vue.component('backup-codes',BackupCodes);

            Vue.component('wp2sv-emails',Emails);
        }
    };
    module.init();
    return module;
});
