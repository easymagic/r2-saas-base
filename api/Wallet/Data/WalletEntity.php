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



}