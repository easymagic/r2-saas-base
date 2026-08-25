<?php 
namespace Wallet\Data;

use Shared\AbstractBaseEntity;

class WalletEntity extends AbstractBaseEntity {
   
   public int $user_id = 0;
   public string $reference = '';
   public string $type = '';
   public float $amount = 0;
   public float $balance = 0;
   public string $description = '';
   public string $payment_url = '';
   public string $proof_of_payment_screenshot1 = '';
   public string $proof_of_payment_screenshot2 = '';
   public string $proof_of_payment_screenshot3 = '';
   public string $reason = '';
   public int $action_by = 0;
   public string $action_at = '';
   public string $status = 'pending'; // pending, approved, rejected
   public string $created_at = '';


   public function __construct(array $attributes = [])
   {
      parent::__construct($attributes);
      $this->handleNullAttributes(empty($this->created_at));
   }

   private function handleNullAttributes(bool $condition){
      if($condition){
         $this->created_at = date('Y-m-d H:i:s');
      }
   }
}