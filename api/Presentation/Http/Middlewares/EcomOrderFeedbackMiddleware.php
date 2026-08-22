<?php

namespace Presentation\Http\Middlewares;

use EcomOrder\Business\Usecases\CronProcessCardPaymentFeedback;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;

class EcomOrderFeedbackMiddleware implements MiddlewareServiceInterface
{
    private CronProcessCardPaymentFeedback $cronProcessCardPaymentFeedback;

    public function __construct(
        CronProcessCardPaymentFeedback $cronProcessCardPaymentFeedback
    ) {
        $this->cronProcessCardPaymentFeedback = $cronProcessCardPaymentFeedback;
    }

    public function handle() {
        $this->cronProcessCardPaymentFeedback->execute();
    }
}
