<?php 
namespace App\Domain\ProxyOrder;

class ProxyOrderEntity
{
    public int $id;
    public int $user_id;
    public string $type;
    public string $reference;
    public string $link;
    public string $screen_shot1;
    public string $screen_shot2;
    public string $screen_shot3;
    public string $description;
    public string $status;
    public string $total_amount_naira;
    public string $total_amount_usd;
    public string $grand_total_naira;

    public float $shipping_cost_naira;
    public float $dollar_to_naira_rate;
    public float $service_charge_fee;
    

}