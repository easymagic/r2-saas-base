<?php 
namespace Data\ProxyOrder\Order;

use Data\AbstractBaseEntity;

class ProxyOrderEntity extends AbstractBaseEntity
{
    public int $id;
    public int $user_id;
    public int $batch_id;
    public int $agent_id;

    public string $type;
    public string $reference;
    public string $link;
    public string $screen_shot1;
    public string $screen_shot2;
    public string $screen_shot3;
    public string $description;
    public string $status; // 'pending','paid','placed','shipped-to-facility','arrived-at-facility','shipped-to-destination-country','arrived-at-destination-country','arrived-at-destination-facility','ready-for-pickup','delivered','cancelled'
    
    public string $total_amount_usd;
    public string $grand_total_naira;

    public float $shipping_cost_usd;
    public float $dollar_to_naira_rate;
    public float $service_charge_usd;

    public string $created_at;
    public string $updated_at;

    public int $pickup_otp_code;

    public int $price_adjustment_sent;

    public int $approve_payment;
    
}