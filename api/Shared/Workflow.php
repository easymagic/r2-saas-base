<?php 
namespace Shared;

use Exception;

class Workflow{
   
  private bool $shouldFlow = true;
  private bool $link = false;


   public function and(){
       $this->link = true;
       return $this;
   }

   public function or(){
    $this->link = false;
    return $this;
   }

   public function flowShouldContinue(bool $condition){
     if ($this->link){
        $result = $this->shouldFlow && $condition;
        $this->shouldFlow = $result;
        return $result;
     }
     $this->shouldFlow = $condition;
     return $this->shouldFlow;
   }

   public function pass(){
    return $this->shouldFlow;
   }

   public function end(){
     $this->link = false;
     return $this;
   }

   function reject(string $errorMessage)
   {
       if ($this->pass()) {
           throw new Exception($errorMessage);
       }
       return $this;
   }

}