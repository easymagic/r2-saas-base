<?php 
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use Shared\Query\QueryObject;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;


class GetAllSchedulesForOrder
{
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;

    public function __construct(
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository
    ) {
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
    }

    /**
     * @param int $order_id
     * @return QueryObject<BnplPaymentScheduleEntity>
     */
    function execute(int $order_id){
        return $this->bnplPaymentScheduleRepository->query(['order_id' => $order_id]);
    }
}