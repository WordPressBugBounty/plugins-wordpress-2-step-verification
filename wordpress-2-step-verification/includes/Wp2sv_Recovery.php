<?php
class Wp2sv_Recovery{
    var $key;
    var $hash;
    var $id;

    protected $wp2sv;
    /**
     * @var Wp2sv_Handler
     */
    protected $handler;
    function __construct(Wordpress2StepVerification $wp2sv){
        $this->wp2sv=$wp2sv;
        $this->handler=$this->wp2sv->make('handler');
        if(isset($_REQUEST['wp2sv_recovery_key'])){
            $this->key=$_REQUEST['wp2sv_recovery_key'];
        }else {
            $this->key = wp_generate_password(12, false);
        }
        $token=wp_salt();
        if(function_exists('wp_get_session_token')){
            $token=wp_get_session_token();
        }
        $this->hash=wp_hash( $this->key . '|' . $token, 'nonce' );
        $this->id=substr($this->hash, -12, 10);
    }
    function handle(){
        if($this->handler->post('wp2sv_recovery_key')){
            if($this->handler->post('wp2sv_recovery_download')){
                $this->download();
                die;
            }
            if($this->handler->post('wp2sv_recovery_verify')){
                if($this->verify()===1){
                    $this->handler->passed();
                }else{
					/* translators: %s is replaced with wordpress path */
                    $this->handler->error(sprintf(__('Could not verify the file, please make sure the file is uploaded to %s','wordpress-2-step-verification'),ABSPATH));
                }
            }
        }
    }
    function verify(){
        $file=ABSPATH.$this->getFileName();
        if(file_exists($file)){
            if(@file_get_contents($file)===$this->hash){
                @unlink($file);
                return 1;
            }
            else{
                return 0;
            }
        }
        return false;
    }

    function getFileName(){
        return 'wp2sv'.$this->id.'.html';
    }
    function fileName(){
        echo $this->getFileName();
    }
    function key(){
        echo $this->key;
    }
    function download(){
        header('Content-type: application/octetstream');
        header('Content-Length: ' . strlen($this->hash));
        header('Content-Disposition: attachment; filename="' . $this->getFileName() . '"');
        echo $this->hash;
        die;
    }

}
