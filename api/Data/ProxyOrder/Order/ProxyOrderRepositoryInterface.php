<?php

namespace Data\ProxyOrder\Order;

use Data\ProxyOrder\Order\ProxyOrderEntity;

interface ProxyOrderRepositoryInterface
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
   const ALLOWED_TYPES = ['online', 'physical'];


   /**
    * @return array
    */
   function fetch();
   /**
    * @return int
    */
   function count();
   /**
    * @param string $column
    * @return float
    */
   function sum(string $column);
   /**
    * @param array $filters
    * @return self
    */
   function filter(array $filters);
   /**
    * @param int $id
    * @return ProxyOrderEntity
    */
   function find(int $id);
   function save(int $id, array $data);
   function delete(int $id);
   /**
    * @param int $userId
    * @return self
    */
   function filterByUserId(int $userId);
   /**
    * @param int $agentId
    * @return self
    */
   function filterByAgent(int $agentId);

   /**
    * @param string $status
    * @return self
    */
   function filterByStatus(string $status);

   /**
    * @param int $batchId
    * @return self
    */
   function filterByBatch(int $batchId);

   /**
    * @param string $type
    * @return self
    */
   function filterByType(string $type);

   /**
    * @param string $search
    * @return self
    */
   function filterBySearch(string $search);

   /**
    * @return self
    */
   function filterByPaid();

   /**
    * @return self
    */
   function filterByPending();
}
