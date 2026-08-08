<?php

namespace SnappyOrder\Data;

use Shared\AbstractBaseEntity;

class SnappyOrderEntity extends AbstractBaseEntity
{
    public int $user_id = 0;
    public int $batch_id = 0;
    public int $agent_id = 0;
    public string $type = '';
    public string $reference = '';
    public string $link = '';
    public string $screen_shot1 = '';
    public string $screen_shot2 = '';
    public string $screen_shot3 = '';
    public string $description = '';
    public string $status = '';
    public string $total_amount_usd = '';
    public string $grand_total_naira = '';
    public float $shipping_cost_usd = 0;
    public float $dollar_to_naira_rate = 0;
    public float $service_charge_usd = 0;
    public string $created_at = '';
    public string $updated_at = '';
    public int $pickup_otp_code = 0;
    public int $approve_payment = 0;
    public int $price_adjustment_sent = 0;
}
