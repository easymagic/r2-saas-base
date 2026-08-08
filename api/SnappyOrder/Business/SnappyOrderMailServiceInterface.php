<?php 
namespace SnappyOrder\Business;

interface SnappyOrderMailServiceInterface
{
    public function notifyCustomerOfOrderCreation(int $order_id);
    public function notifyAdminOfOrderCreation(int $order_id);
    public function notifyCustomerOfStatusChange(int $order_id, string $status);
    public function notifyCustomerOfOrderPayment(int $order_id);
    public function notifyCustomerOfPickupOTP(int $order_id, string $otp);
    public function notifyAgenOfOrderAssignment(int $order_id, int $agent_id);
    public function notifyCustomerOfAgentAssignment(int $order_id, int $agent_id);
    public function notifyCustomerOfPriceChange(int $order_id, float $price);
}