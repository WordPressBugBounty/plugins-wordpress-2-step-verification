<template>
    <div class="card-content">
        <div class="card-body">
            <div class="wp2sv-row" v-if="enroll">
                <div class="wp2sv-col-0 card-icon">
                    <div class="wp2sv-logo"></div>
                </div>
                <div class="wp2sv-col">
                    <div class="wp2sv-h1">
                        {{i18n.__('Protect your account with 2-Step Verification', 'wordpress-2-step-verification') }}
                    </div>
                    <p>
                        {{
                            i18n.__('Each time you sign in to your account, you\'ll need your password and a verification code. ', 'wordpress-2-step-verification')
                        }}</p>
                </div>
            </div>
            <div v-if="step==='select-device'">
                <div class="wp2sv-h1">{{
                        i18n.__('Get codes from the Authenticator app', 'wordpress-2-step-verification')
                    }}
                </div>
                <div class="wp2sv-text">
                    {{
                        i18n.__('Instead of waiting for text messages, get verification codes for free from the Authenticator app. It works even if your phone is offline.')
                    }}
                </div>
                <div class="wp2sv-h2">{{i18n.__('What kind of phone do you have?', 'wordpress-2-step-verification') }}</div>
                <div class="wp2sv-p"><label><input type="radio" value="android" v-model="device"> Android</label></div>
                <div class="wp2sv-p"><label><input type="radio" value="iphone" v-model="device"> Iphone</label></div>
            </div>
            <div v-if="step==='setup'">
                <div class="wp2sv-h1">{{i18n.__('Set up Authenticator', 'wordpress-2-step-verification') }}</div>
                <ol class="wp2sv-p" v-if="device==='android'">
                    <!--suppress HtmlUnknownTarget -->
                    <li v-html="sprintf(i18n.__('Get the Authenticator App from the %s.','wordpress-2-step-verification'), sprintf('<a target=&quot;_blank&quot; href=&quot;%s&quot;>%s</a>', 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2', i18n.__('Play Store','wordpress-2-step-verification')))"></li>
                    <li v-html="i18n.__('In the App select <b>Set up account.</b>','wordpress-2-step-verification')"></li>
                    <li v-html="i18n.__('Choose <b>Scan a barcode.</b>','wordpress-2-step-verification')"></li>
                    <li style="list-style-type: none">
                        <div class="wp2sv-center">
                            <div class="wp2sv-qrcode">
                                <img alt="QR Code" v-if="qr_url" class="wp2sv-qrcode-img" :src="qr_url">
                                <div v-if="qr_data" class="wp2sv-qrcode-render"></div>
                            </div>
                            <span class="wp2sv-action"
                                  @click="manually">{{i18n.__("Can't scan it?", 'wordpress-2-step-verification') }}</span>
                        </div>
                    </li>

                </ol>


                <ol class="wp2sv-p" v-else>
                    <!--suppress HtmlUnknownTarget -->
                    <li v-html="sprintf(i18n.__('Get the Authenticator App from the %s.','wordpress-2-step-verification'), sprintf('<a target=&quot;_blank&quot; href=&quot;%s&quot;>%s</a>', 'https://itunes.apple.com/en/app/google-authenticator/id388497605', i18n.__('App Store','wordpress-2-step-verification') ) )"></li>
                    <li v-html="i18n.__('In the App select <b>Set up account.</b>','wordpress-2-step-verification')"></li>
                    <li v-html="i18n.__('Choose <b>Scan a barcode.</b>','wordpress-2-step-verification')"></li>
                    <li style="list-style-type: none">
                        <div class="wp2sv-center">
                            <div class="wp2sv-qrcode">
                                <img alt="QR Code" v-if="qr_url" class="wp2sv-qrcode-img" :src="qr_url">
                                <div v-if="qr_data" class="wp2sv-qrcode-render"></div>
                            </div>
                            <span class="wp2sv-action"
                                  @click="manually">{{i18n.__("Can't scan it?", 'wordpress-2-step-verification') }}</span>
                        </div>
                    </li>
                </ol>

            </div>
            <div v-else-if="step==='manually-setup'">
                <div class="wp2sv-h1">{{i18n.__("Can't scan the barcode?", 'wordpress-2-step-verification') }}</div>

                <ol class="wp2sv-p">
                    <li v-html="i18n.__('Tap <b>Menu</b>, then <b>Set up account</b>.','wordpress-2-step-verification')"></li>
                    <li v-html="i18n.__('Tap <b>Enter provided key</b>.','wordpress-2-step-verification')"></li>
                    <li>{{i18n.__('Enter your username and this key:','wordpress-2-step-verification')}}</li>
                    <li style="list-style-type: none">
                        <div class="wp2sv-bd wp2sv-text-center">
                            <div class="wp2sv-bb">{{ formatted_secret }}</div>
                            <br>{{i18n.__("spaces don't matter", 'wordpress-2-step-verification') }}
                        </div>
                    </li>
                    <li v-html="i18n.__('Make sure <b>Time based</b> is turned on, and tap <b>Add</b> to finish.','wordpress-2-step-verification')"></li>
                </ol>

            </div>

            <div v-else-if="step==='test'">
                <div class="wp2sv-h1">{{i18n.__('Set up Authenticator', 'wordpress-2-step-verification') }}</div>
                <div class="wp2sv-p">
                    {{i18n.__('Enter the 6-digit code you see in the app.', 'wordpress-2-step-verification') }}
                </div>
                <div class="wp2sv-form-group" :class="error_code?'wp2sv-error':''">
                    <label>
                        <input type="text" id="test-code" v-model="code" maxlength="6" placeholder="Enter code">
                    </label>
                    <div v-if="error_code">{{ error_code }}</div>
                </div>
            </div>

            <div v-else-if="step==='complete'">
                <div class="wp2sv-h1">{{i18n.__('Done!', 'wordpress-2-step-verification') }}</div>
                <div class="wp2sv-p">
                    {{
                        i18n.__("You're all set. From now on, you'll use Authenticator to sign in to your Wordpress Account.", 'wordpress-2-step-verification')
                    }}
                </div>
            </div>

            <div v-else-if="step==='turn-on'">
                <div class="wp2sv-h1">{{
                        i18n.__('It worked! Turn on 2-Step Verification?', 'wordpress-2-step-verification')
                    }}
                </div>
                <p>
                    {{
                        i18n.__('Now that you\'ve seen how it works, do you want to turn on 2-Step Verification for your Wordpress Account?', 'wordpress-2-step-verification')
                    }}</p>
            </div>
        </div>
        <div class="card-footer">
            <div class="wp2sv-row">
                <div class="wp2sv-col" v-if="enroll&&step!=='turn-on'">
                    <span class="wp2sv-action" @click="useEmail">{{
                            i18n.__('Use email', 'wordpress-2-step-verification')
                        }}</span>
                </div>
                <div class="pull-right" v-if="enroll">
                    <button class="wp2sv-btn wp2sv-btn-primary" v-if="step==='manually-setup'" @click="back">
                        {{i18n.__('Back', 'wordpress-2-step-verification') }}
                    </button>

                    <button class="wp2sv-btn wp2sv-btn-primary" @click="next" v-if="step==='turn-on'">
                        {{i18n.__('Turn on', 'wordpress-2-step-verification') }}
                    </button>
                    <button class="wp2sv-btn wp2sv-btn-primary" @click="next" v-else-if="step==='test'">
                        {{i18n.__('Verify', 'wordpress-2-step-verification') }}
                    </button>
                    <button class="wp2sv-btn wp2sv-btn-primary" @click="next" v-else>
                        {{i18n.__('Next', 'wordpress-2-step-verification') }}
                    </button>
                </div>
                <div class="pull-right" v-else>
                    <span class="wp2sv-action" v-if="step==='manually-setup'"
                        @click="back">{{i18n.__('Back', 'wordpress-2-step-verification') }}</span>
                    <span class="wp2sv-action wp2sv-modal-close"
                          v-else-if="step!=='complete'">{{i18n.__('Cancel', 'wordpress-2-step-verification') }}</span>

                    <span class="wp2sv-action" @click="next"
                          v-if="step==='complete'">{{i18n.__('Done', 'wordpress-2-step-verification') }}</span>
                    <span class="wp2sv-action" @click="next"
                          v-else-if="step==='test'">{{i18n.__('Verify', 'wordpress-2-step-verification') }}</span>
                    <span class="wp2sv-action" @click="next" v-else>{{
                            i18n.__('Next', 'wordpress-2-step-verification')
                        }}</span>
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
    props: ['enroll'],
    data: function () {
        return {
            step: 'select-device',
            device: 'android',
            error_code: '',
            code: '',
            qr_size: 128,
            qr_data:'',
            qr_url: '',
            secret: '',
        };
    },
    mixins: [i18n],
    mounted: function () {
        let $modal = $(this.$el).closest('.wp2sv-modal');
        let self = this;
        $modal.on('close', function () {
            self.reset();
        });
    },
    watch: {
        step: function () {
            if (this.step === 'setup') {
                this.loadQrCodes();
            }
        }
    },
    computed: {
        formatted_secret: function () {
            return this.secret.replace(/(.{4})/g, '$1 ').trim()
        }
    },
    methods: {
        next: function () {
            let self = this;
            switch (this.step) {
                case 'select-device':
                    this.step = 'setup';
                    break;
                case 'setup':
                case 'manually-setup':
                    this.step = 'test';
                    break;
                case 'test':
                    this.testCode();
                    break;
                case 'complete':
                    store.set('mobile_dev', this.device);
                    wp2sv.closeModal();
                    break;
                case 'turn-on':
                    this.disabled = true;
                    wp2sv.post({
                        action: 'enable',
                        code: this.code,
                        secret: this.secret,
                        device: this.device
                    }).done(function (result) {
                        if (result && result.success) {
                            return store.load();
                        } else {
                            store.reload();
                        }
                    }).always(function () {
                        self.disabled = false;
                    });
                    break;
            }

        },
        testCode: function () {
            let self = this;
            if (!this.code) {
                this.error_code = this.i18n.__('Please enter your verification code.','wordpress-2-step-verification');
                return;
            }
            this.disabled = true;
            wp2sv.post({
                action: 'test-code',
                code: this.code,
                secret: this.secret,
                changeDevice: this.enroll ? '' : 1,
                device: this.device
            }).done(function (result) {
                if (!result) {
                    return;
                }
                if (result.success) {
                    if (self.enroll) {
                        self.step = 'turn-on';
                    } else {
                        self.step = 'complete';
                    }
                } else {
                    self.error_code = self.i18n.__('Invalid code. Please try again.','wordpress-2-step-verification');
                }
            }).always(function () {
                self.disabled = false;
            });
        },
        back: function () {
            this.step = 'setup';
        },
        manually: function () {
            this.step = 'manually-setup';
        },
        reset: function () {
            $.extend(this.$data, {
                step: 'select-device',
                device: 'android',
                error_code: '',
                code: '',
                qr_size: 128,
                qr_data:'',
                qr_url: '',
                secret: ''
            });
        },
        loadQrCodes: function () {
            let self = this;
            wp2sv.get('qrcode').then(function (result) {
                if (result && result.success) {
                    return result.data;
                }
                return false;
            }).then(function (data) {
                if (data) {
                    self.qr_data = data.data;
                    self.qr_size = data.size || 128;
                    self.secret = data.secret;
                    self.$nextTick(function () {
                        if(!self.renderQrCodes()){
                            //Not able to render QR code, fallback to image url
                            self.qr_url = data.url;
                        }
                    });


                }
            });
        },
        renderQrCodes(){
            let size=this.qr_size || 128;
            let qrCodeDivs=document.getElementsByClassName('wp2sv-qrcode-render');
            if(qrCodeDivs.length===0 || typeof QRCode==='undefined'){
                console.log('No QR code divs found or no data to render QR code',qrCodeDivs.length,typeof QRCode==='undefined',qrCodeDivs.length===0 || typeof QRCode==='undefined')
                return false;
            }
            try{
                for(let i=0;i<qrCodeDivs.length;i++){
                    new QRCode(qrCodeDivs[i], {
                        text: this.qr_data,
                        width: size,
                        height: size,
                        correctLevel : QRCode.CorrectLevel.M,
                    });
                }
                return true;
            }catch(e){
                return false;
            }
        },
        useEmail: function (e) {
            e && e.preventDefault();
            this.$root.$emit('enroll:email-flow');
        }

    }
}
</script>
