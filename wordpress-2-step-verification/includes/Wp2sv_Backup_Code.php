<?php
class Wp2sv_Backup_Code extends Wp2sv_Base {
    protected $backup_codes;
    protected $max=10;

    function getCodes($get='all'){
        if(!isset($this->backup_codes)) {
            $this->backup_codes = $this->model->backup_codes;
            if(!is_array($this->backup_codes)){
                $this->backup_codes=array();
            }

            if(!empty($this->backup_codes['codes'])){
                $unused=0;
                $total=0;
                foreach($this->backup_codes['codes'] as $code=>$is_unused){
                    $total++;
                    if($is_unused){
                        $unused++;
                    }
                }
                $this->backup_codes['unused']=$unused;
                $this->backup_codes['total']=$total;
            }


        }
        if($get==='all'){
            return $this->backup_codes;
        }
        return isset($this->backup_codes[$get])?$this->backup_codes[$get]:null;
    }

    /**
     * @param $code
     * @return int 1 if valid 0 if invalid -1 if already used
     */
    function check($code){
        $code=trim(str_replace(' ','',$code));
        $backup_codes=$this->getCodes('codes');
        if(!isset($backup_codes[$code])){
            $result=0;
        }else{
            if($backup_codes[$code]){
                $result=1;
                $backup_codes[$code]=0;
                $this->backup_codes['codes']=$backup_codes;
                $this->model->backup_codes=$this->backup_codes;
            }else{
                $result=-1;
            }
        }
        return $result;
    }
    function generate($length=8,$total_codes=10){
        $codes=array();
        for($i=0;$i<$total_codes;$i++){
            $code='';
            for($j=0;$j<$length;$j++){
                $code.=mt_rand(0,9);
            }
            $codes[$code]=1;
        }
        $wp2sv_backup_codes=array('codes'=>$codes,'last'=>current_time('mysql'));
        $this->model->backup_codes=$wp2sv_backup_codes;
        $this->backup_codes=null;
    }
    function failed(){
        $failed=$this->model->backup_failed;
        $failed=absint($failed);
        $this->model->backup_failed=$failed+1;
    }
    function isLocked(){
        $failed=$this->model->backup_failed;
        $failed=absint($failed);
        return $failed>=$this->max;
    }
    function download(){
        $template=WP2SV_TEMPLATE.'/setup/backup-codes.txt.php';
        if(file_exists($template)&&is_readable($template)) {
            /** @noinspection PhpIncludeInspection */
            include $template;
            die;
        }
    }
}