<?php 
namespace Application\ProxyOrder\Thread;

interface ProxyOrderThreadServiceInterface
{
    // public int $id;
    // public int $proxy_order_id;
    // public int $sender_id;
    // public string $message;
    // public string $attachment_url;
    // public string $created_at;
    // public string $updated_at;

    function create(int $proxy_order_id, int $sender_id, string $message, array $attachment_url);
    function fetchByOrderId(int $orderId);
    function delete(int $id);
}