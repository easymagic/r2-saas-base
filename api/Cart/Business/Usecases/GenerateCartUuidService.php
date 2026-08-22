<?php
namespace Cart\Business\Usecases;

class GenerateCartUuidService
{
    public function execute()
    {
        return uniqid('cart_' . time() . '_' . date('Y-m-d-H-i-s'), true);
    }
}
