<?php 
namespace Application\MailNotifications\Thread;

interface ThreadMailNotificationInterface
{
    function sendToCustomer(int $proxyOrderId);
}