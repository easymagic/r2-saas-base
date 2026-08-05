<?php 
namespace Business\ProxyOrder\Thread;

interface ProxyOrderThreadServiceInterface
{
    function create(int $proxy_order_id, int $sender_id, string $message, array $attachment_url);
    function fetchByOrderId(int $orderId);
    function delete(int $id);
}