<?php

/**
 * Class Wp2sv_Base

 */
class Wp2sv_Base extends Wp2sv_Abstract {
    function isEnabled($user_id=null){
        if($user_id!==null){
            return Wp2sv_Model::forUser($user_id)->enabled;
        }
        return $this->model->enabled;
    }


}