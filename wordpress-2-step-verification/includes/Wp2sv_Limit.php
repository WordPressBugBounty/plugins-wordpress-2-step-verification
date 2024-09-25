<?php


class Wp2sv_Limit
{
	protected $model;
	static protected $instances=[];
	protected $maxAttempts=0;
	protected $attemptLockMinutes=0;
	protected $maxAttemptsEmail=0;
	protected $attemptEmailLockMinutes=0;
	public function __construct(Wp2sv_Model $model)
	{
		$this->model=$model;
		$this->maxAttempts=absint(wp2sv_setting('max_attempts',5));
		$this->attemptLockMinutes=absint(wp2sv_setting('attempts_lock',15));

        $this->maxAttemptsEmail=absint(wp2sv_setting('max_emails',10));
		$this->attemptEmailLockMinutes=absint(wp2sv_setting('emails_lock',30));
	}
	public function hasAttemptsLimit(){
		return $this->maxAttempts && $this->attemptLockMinutes;
	}
	function hasEmailAttemptsLimit(){
		return $this->maxAttemptsEmail && $this->attemptEmailLockMinutes;
	}
	public function attempt(){
		if($this->hasAttemptsLimit()){
			$this->model->attempts++;
			if($this->model->attempts>=$this->maxAttempts){
				$this->model->locked_until=time()+$this->attemptLockMinutes*60;
			}
		}
	}
	public function isLocked(){
		if($this->model->locked_until){
			if($this->model->locked_until > time()){
				return true;
			}else{
				$this->model->locked_until='';
				$this->model->attempts=0;//Clear attempts
			}
		}
		return false;
	}
	public function willBeUnlockIn(){
		return $this->humanTimeDiff($this->model->locked_until-time());
	}
	public function sendMailWillBeUnlockIn(){
		return $this->humanTimeDiff($this->model->email_locked_until-time());
	}
	public function attemptEmail(){
		if($this->hasEmailAttemptsLimit()) {
			$this->model->email_sent++;
			if ($this->model->email_sent >= $this->maxAttemptsEmail) {
				$this->model->email_locked_until = time() + $this->attemptEmailLockMinutes * 60;
			}
		}
	}
	public function isLockedEmail(){
		if($this->model->email_locked_until){
			if($this->model->email_locked_until > time()){
				return true;
			}else{
				$this->model->email_locked_until='';
				$this->model->email_sent=0;//Clear attempts
			}
		}
		return false;
	}

	function attemptsLeft(){
		$left=$this->maxAttempts-absint($this->model->attempts);
		return max($left,0);
	}
	function emailAttemptsLeft(){
		$left=$this->maxAttemptsEmail-absint($this->model->email_sent);
		return max($left,0);
	}
	protected function humanTimeDiff($seconds){
		if($seconds>60){
			/* translators: %s: number of minutes */
			return sprintf(__('%s minute(s)','wordpress-2-step-verification'),round($seconds/60));
		}else{
			/* translators: %s: number of seconds */
			return sprintf(__('%s seconds(s)','wordpress-2-step-verification'),round($seconds));
		}
	}
	/**
	 * @param Wp2sv_Model $model
	 * @return static
	 */
	public static function forUser(Wp2sv_Model $model){
		if(!isset(static::$instances[$model->ID])){
			static::$instances[$model->ID]=new static($model);
		}
		return static::$instances[$model->ID];
	}
}
