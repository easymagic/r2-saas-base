<?php 
namespace Shared;

use Exception;
use Shared\AbstractBaseEntity;

class Contracts{
  
   /**
    * Check if the value is not null
    * @param mixed $value
    * @param string $argumentName
    * @return void
    * @throws Exception
    */
   public static function requiresNotNull(mixed $value, string $argumentName){
    if (is_null($value)){
        throw new Exception("The argument $argumentName is required");
    }
   }

   /**
    * Check if the value is not null or empty
    * @param mixed $value
    * @param string $argumentName
    * @return void
    * @throws Exception
    */
   public static function requiresNotNullOrEmpty(mixed $value, string $argumentName){
    if (is_null($value) || empty($value)){
        throw new Exception(ucfirst($argumentName) . " is required and cannot be empty!");
    }
   }

   /**
    * Check if the condition is true
    * @param bool $condition
    * @param string $message
    * @return void
    * @throws Exception
    */
   public static function requires(bool $condition, string $message){
    if (!$condition){
        throw new Exception($message);
    }
   }

   /**
    * Check if the value is in the array
    * @param mixed $value
    * @param array $array
    * @param string $argumentName
    * @return void
    * @throws Exception
    */
   public static function requiresInArray(mixed $value, array $array, string $argumentName){
    if (!in_array($value, $array)){
        throw new Exception("The argument $argumentName must be one of the following values: " . implode(', ', $array));
    }
   }

   /**
    * Check if the entity is found
    * @param AbstractBaseEntity $entity
    * @param string $argumentName
    * @return void
    * @throws Exception
    */
   public static function requireEntityFound(AbstractBaseEntity $entity, string $argumentName){
    if ($entity->isEmpty()){
        throw new Exception("$argumentName not found!");
    }
   }




}