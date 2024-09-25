<template>
    <div class="card-content">
        <div class="card-body">
            <div class="wp2sv-row" v-if="enroll">
                <div class="wp2sv-col-0 card-icon">
                    <div class="wp2sv-logo"></div>
                </div>
                <div class="wp2sv-col">
                    <div class="wp2sv-h1">{{i18n.__('Protect your account with 2-Step Verification', 'wordpress-2-step-verification')}}</div>
                    <p>{{i18n.__('Each time you sign in to your account, you\'ll need your password and a verification code. ', 'wordpress-2-step-verification')}}</p>
                </div>
            </div>
            <div v-if="step==='edit'">
                Edit...
            </div>
            <div v-else-if="step==='email'">
                <div class="wp2sv-h1">{{i18n.__('Let\'s set up your email', 'wordpress-2-step-verification')}}</div>
                <p>{{i18n.__('What email address do you want to use?','wordpress-2-step-verification')}}</p>
                <div class="wp2sv-form-group" :class="error_email?'wp2sv-error':''">
                    <label for="email" class="has-float-label">
                        <input type="text" id="email" v-model="email" required/>
                    </label>
                    <div v-if="error_email" v-html="error_email"></div>
                </div>
            </div>
            <div v-else-if="step==='test'">
                <div class="wp2sv-h1">{{i18n.__('Confirm that it works','wordpress-2-step-verification')}}</div>
                <div class="wp2sv-p">{{i18n.__('Wp2sv just sent an email with a verification code to','wordpress-2-step-verification')}} {{email}}.</div>
                <div class="wp2sv-form-group" :class="error_code?'wp2sv-error':''">
                    <label for="code" class="has-float-label">
                        <input type="text" id="code" v-model="code" required :placeholder="i18n.__('Enter the code','')"/>
                    </label>
                    <div v-if="error_code">{{error_code}}</div>
                </div>

                <p>{{i18n.__('Didn\'t get it?','wordpress-2-step-verification')}} <a href="" @click="startOver">{{i18n.__('Resend','wordpress-2-step-verification')}}</a></p>
            </div>

            <div v-else-if="step==='turn-on'">
                <div class="wp2sv-h1">{{i18n.__('It worked! Turn on 2-Step Verification?','wordpress-2-step-verification')}}</div>
                <p>{{i18n.__('Now that you\'ve seen how it works, do you want to turn on 2-Step Verification for your Wordpress Account?','wordpress-2-step-verification')}}</p>
            </div>

            <div v-else-if="step==='complete'">
                <div class="wp2sv-h1">{{i18n.__('Done!','wordpress-2-step-verification')}}</div>
                <div class="wp2sv-p">{{i18n.__("You're all set. From now on, you'll use Email to sign in to your Wordpress Account.",'wordpress-2-step-verification')}}</div>
            </div>

        </div>
        <div class="card-footer">
            <div class="wp2sv-row">
                <div class="wp2sv-col" v-if="enroll && step!=='turn-on'" >
                    <span class="wp2sv-action" @click="useApp">{{i18n.__('Use app','wordpress-2-step-verification')}}</span>
                </div>
                <div class="pull-right" v-if="enroll">
                    <button class="wp2sv-btn wp2sv-btn-primary" @click="next" v-if="step==='turn-on'" :disabled="disabled">{{i18n.__('Turn on','wordpress-2-step-verification')}}</button>
                    <button class="wp2sv-btn wp2sv-btn-primary" @click="next" v-else :disabled="disabled">{{i18n.__('Next','wordpress-2-step-verification')}}</button>
                </div>
                <div class="pull-right" v-else>
                    <span class="wp2sv-action wp2sv-modal-close" v-if="step!=='complete'">{{i18n.__('Cancel','wordpress-2-step-verification')}}</span>
                    <span class="wp2sv-action" @click="next" v-if="step==='test'">{{i18n.__('Done','wordpress-2-step-verification')}}</span>
                    <span class="wp2sv-action" @click="next" v-else>{{i18n.__('Next','wordpress-2-step-verification')}}</span>
                </div>


            </div>
        </div>
    </div>
</template>
<script>
    import $ from '../libs/jquery';
    import i18n from "../libs/i18n";
    import store from "../libs/store";
    export default {
        mixins: [i18n],
        props:['enroll'],
        data:function(){
            return {
                step:'email',
                email:'',
                code:'',
                error_email:'',
                error_code:'',
                disabled:false
            };
        },
        mounted:function(){
            let $modal=$(this.$el).closest('.wp2sv-modal');
            let self=this;
            $modal.on('close',function(){
                self.reset();
            });
        },
        methods:{
            useApp:function(){
                this.$root.$emit('enroll:app-flow');
            },
            startOver:function(e){
                e&&e.preventDefault();
                this.step='email';
            },
            next:function(){
                let self=this;
                switch (this.step){
                    case 'email':
                        this.error_email='';

                        if(!this.email){
                            this.error_email=this.i18n.__('Invalid email, try again.');
                        }else{
                            this.disabled=true;
                            wp2sv.toast.info(this.i18n.__('Working...','wordpress-2-step-verification'));
                            wp2sv.post({action:'send-email','email':self.email}).done(function(data){
                                if(!data){
                                    return;
                                }
                                if(data && data.success){
                                    self.step='test';
                                }else{
                                    if(data.data&&data.data.message){
                                        self.error_email=data.data.message;
                                    }
                                }
                            }).fail(function(){
                                wp2sv.toast.error(self.i18n.__('Failed','wordpress-2-step-verification'));
                            }).always(function(){
                                self.disabled=false;
                                wp2sv.toast.hide();
                            });

                        }

                        break;
                    case 'test':
                        this.disabled=true;

                        wp2sv.post({
                            action:'test-code',
                            code:this.code,
                            email:this.email,
                            updateEmail:!this.enroll
                        }).done(function(result){
                            if(!result){
                                return ;
                            }
                            if(result.success){
                                if(self.enroll) {
                                    self.step = 'turn-on';
                                }else {
                                    store.set('emails',result.data.emails);
                                    self.complete();
                                }
                            }else{
                                self.error_code=self.i18n.__('Invalid code. Please try again.','wordpress-2-step-verification');
                            }
                        }).always(function(){
                            self.disabled=false;
                        });
                        break;
                    case 'turn-on':
                        this.disabled=true;
                        wp2sv.post({action:'enable','code':this.code,'email':this.email}).then(function(result){
                            if(result&&result.success){
                                return store.load();
                            }else{
                                store.reload();
                            }
                        }).always(function(){
                            self.disabled=false;
                        });
                        break;
                    case 'complete':
                        wp2sv.closeModal();
                        break;

                }

            },
            complete:function(){
                wp2sv.closeModal();
            },
            reset:function(){
                $.extend(this.$data,{
                    step:'email',
                    email:'',
                    code:'',
                    error_email:'',
                    error_code:'',
                    disabled:false
                });
            },
            cancel:function(){
                this.$root.$emit('enroll:cancel');
            }
        }
    }
</script>
