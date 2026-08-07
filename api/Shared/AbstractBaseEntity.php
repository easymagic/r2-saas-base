<?php 
namespace Shared;

abstract class AbstractBaseEntity {
    public int $id = 0;


    function __construct($attributes = []){
        foreach($attributes as $key => $value){
            if(property_exists($this, $key)){
                 if (!is_null($value)){
                    $this->$key = $value;
                 }else{
                    
                 }
                
            }
        }
    }

    /**
     * Check if the entity is empty
     * @return bool
     */
    function isEmpty(){
        return empty($this->id);
    }
}