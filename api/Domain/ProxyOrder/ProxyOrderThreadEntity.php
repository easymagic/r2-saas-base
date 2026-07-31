<?php 
namespace Domain\ProxyOrder;

class ProxyOrderThreadEntity
{
    public int $id;
    public int $proxy_order_id;
    public int $sender_id;
    public string $message;
    public string $attachment_url;
    public string $created_at;
    public string $updated_at;
    
    
    
}