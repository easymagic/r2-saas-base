<?php

namespace Data\ProxyOrder\Order;

use Data\AbstractBaseRepositoryInterface;

interface ProxyOrderRepositoryInterface extends AbstractBaseRepositoryInterface
{
   const ALLOWED_STATUSES = [
      'pending',
      'paid',
      'placed',
      'shipped-to-facility',
      'arrived-at-facility',
      'shipped-to-destination-country',
      'arrived-at-destination-country',
      'arrived-at-destination-facility',
   ];

   const PAID_STATUSES = [
      'paid',
      'placed',
      'shipped-to-facility',
      'arrived-at-facility',
      'shipped-to-destination-country',
      'arrived-at-destination-country',
      'arrived-at-destination-facility',
      'ready-for-pickup',
      'delivered'
   ];

   const STATUS_ORDER = [
      'pending',
      'paid',
      'placed',
      'shipped-to-facility',
      'arrived-at-facility',
      'shipped-to-destination-country',
      'arrived-at-destination-country',
      'arrived-at-destination-facility',
      'ready-for-pickup',
      'delivered',
      'cancelled'
   ];
   const ALLOWED_TYPES = ['online', 'physical'];
}
