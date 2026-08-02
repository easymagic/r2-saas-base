<?php 
namespace Domain\ProxyOrder\Interfaces;

use Domain\ProxyOrder\ProxyOrderEntity;

interface ProxyOrderRepositoryInterface
{

   function fetch();
   function count();
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

}