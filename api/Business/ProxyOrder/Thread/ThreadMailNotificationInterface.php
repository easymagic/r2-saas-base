<?php 
namespace Business\ProxyOrder\Thread;

interface ThreadMailNotificationInterface
{
    function sendToCustomer(int $proxyOrderId, int $threadId);
}