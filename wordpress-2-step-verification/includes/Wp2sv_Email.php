<?php
/**
 * Created by PhpStorm.
 * User: as247
 * Date: 29-Oct-18
 * Time: 8:05 AM
 */

class Wp2sv_Email extends Wp2sv_Base
{
    protected $autoSendEmailExpiration=180;
	protected function _construct()
	{
		add_filter('wp2sv_mail','Wp2sv_Mailer::send',100,4);
	}


	function getEmailSubject(){
        $blog_name=get_bloginfo('name');
        /* translators: %s is replaced with blog name */
        $subject=__('%s - Your verification code','wordpress-2-step-verification');
        $subject=sprintf($subject,$blog_name);
        return apply_filters('wp2sv_email_subject',$subject);
    }
    function getEmailContent(){
        $code=$this->generate();
		/* translators: %s is replaced with verification code */
        $content= sprintf(__('Your verification code is: %s','wordpress-2-step-verification'),$code);
        return apply_filters('wp2sv_email_content',$content,$code);
    }


    function check($code){
        $code=str_pad($code,6,'0',STR_PAD_LEFT);
        $email_codes=$this->model->email_codes;
        //Check if code expired
        $expiration_minutes=wp2sv_setting('emails_expiration',30);
        $expiration_minutes=absint($expiration_minutes);
        $expiration_minutes=max(1,$expiration_minutes);
        if(isset($email_codes['created_at']) && $email_codes['created_at']+$expiration_minutes*60<current_time('timestamp')){
            $this->model->email_codes=[];
            return false;
        }
        if(isset($email_codes['code']) && $email_codes['code']===$code){
            $this->model->email_codes=[];
            return true;
        }
        return false;
    }

    function generate(){
        $code=rand(0,999999);
        $code=str_pad($code,6,'0',STR_PAD_LEFT);
        $this->model->email_codes=[
            'code'=>$code,
            'created_at'=>current_time('timestamp')
        ];
        return $this->model->email_codes['code'];
    }

    /**
     * Sent code to registered email
     * @param $email
     * @return bool
     */
    function sendCodeToEmail($email){
        if($email) {
            return apply_filters('wp2sv_mail',null,$email, $this->getEmailSubject(), $this->getEmailContent());
        }
        return false;
    }
    function handle(){
        $action=$this->post('wp2sv_action');
        $email=$this->handler->getEmail();

        if($email) {
        	$limiter=Wp2sv_Limit::forUser($this->model);
            $sent = !empty($_COOKIE['wp2sv_email_sent']);
            if ($action == 'send-email'
				||
				($this->handler->getPrimaryMethod() == 'email' && !$sent)) {
                if (!$limiter->isLockedEmail()) {
                    if ($this->sendCodeToEmail($email)) {
                        setcookie('wp2sv_email_sent', 1, time() + $this->autoSendEmailExpiration, '/', COOKIE_DOMAIN);
						$limiter->attemptEmail();
                    } else {
                        $this->handler->error(__('The e-mail could not be sent.','wordpress-2-step-verification').' '.__('Possible reason: your host may have disabled the mail() function...', 'wordpress-2-step-verification'));
                    }
                } else {
					/* translators: %s: Time for unlock */
					$this->handler->error(sprintf(__('You have exceeded maximum send email retries. Please try after %s.','wordpress-2-step-verification'),$limiter->sendMailWillBeUnlockIn()));
                }
            }else{
                $this->handler->error('');
            }
        }

    }
}
