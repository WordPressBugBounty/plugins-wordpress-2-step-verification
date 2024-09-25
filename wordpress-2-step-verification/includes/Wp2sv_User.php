<?php
/**
 * Created by PhpStorm.
 * User: as247
 * Date: 24-Oct-18
 * Time: 9:36 AM
 */

abstract class Wp2sv_User implements ArrayAccess {
    protected $user_id;
    protected $user;
    protected $fillable=[];
    protected $map_attributes=[];
    protected $attributes=[];


    protected function remember($key,$value){
        if(!isset($this->attributes[$key])){
            $this->attributes[$key]=wp2sv_value($value);
        }
        return $this->attributes[$key];
    }

    public function __construct($user_id=0){
        if($user_id===0){
            $user_id=get_current_user_id();
        }
        $user=null;
        if($user_id instanceof WP_User) {
            $user=$user_id;
            $this->user_id=$user->ID;
        }else{
            $this->user_id=$user_id;
            //$user = get_userdata($user_id);
        }
        $this->user=$user;
    }

    #[\ReturnTypeWillChange]
    function offsetGet($offset)
    {
        if(array_key_exists($offset,$this->attributes)){
            return $this->attributes[$offset];
        }
        $value=null;
        if(isset($this->map_attributes[$offset])){
            $mappedOffset=$this->map_attributes[$offset];
            if(!$this->user){
                return false;
            }
            $value = $this->user->$mappedOffset;
        }else {
            $prefixedOffset = static::applyPrefix($offset);
            $value = get_user_meta($this->user_id, $prefixedOffset, true);
        }
        if($this->hasGetMutator($offset)){
            return $this->mutateAttribute($offset,$value);
        }
        return $value;

    }


    #[\ReturnTypeWillChange]
    function offsetExists($offset)
    {
        $offset=static::applyPrefix($offset);
        return get_user_meta($this->user_id,$offset,true);
    }

    #[\ReturnTypeWillChange]
    function offsetSet($offset, $value)
    {
        if(!in_array($offset,$this->fillable)){
            return false;
        }
        $offset=static::applyPrefix($offset);
        if($value === null || $value === false){
            return delete_user_meta($this->user_id,$offset);
        }else {
            return update_user_meta($this->user_id, $offset, $value);
        }
    }
    #[\ReturnTypeWillChange]
    function offsetUnset($offset)
    {
        $offset=static::applyPrefix($offset);
        return delete_user_meta($this->user_id,$offset);
    }
    public static function applyPrefix($offset){
        if(strpos($offset,'wp2sv_')!==0){
            $offset='wp2sv_'.$offset;
        }
        return $offset;
    }

    function __get($offset){
        return $this->offsetGet($offset);
    }
    function __set($offset,$value){
        $this->offsetSet($offset,$value);
    }


    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.wp2sv_str_studly($key).'Attribute');
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get'.wp2sv_str_studly($key).'Attribute'}($value);
    }
}