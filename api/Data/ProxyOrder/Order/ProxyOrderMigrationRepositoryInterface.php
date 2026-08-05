<?php 

namespace Data\ProxyOrder\Order;

interface ProxyOrderMigrationRepositoryInterface
{
    /**
     * @return void
     */
    function migrate();
}