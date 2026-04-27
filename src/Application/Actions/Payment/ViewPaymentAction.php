<?php
declare(strict_types=1);

namespace App\Application\Actions\Payment;

use App\Application\Actions\Action;
use App\Domain\Payment\PaymentRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ViewPaymentAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PaymentRepository $payments,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $paymentId = (int)$this->resolveArg('id');
        $payment = $this->payments->findById($paymentId);

        if (!$payment) {
            throw new HttpNotFoundException($this->request, 'Payment not found');
        }

        $payouts = $this->payments->getPayoutsByDealId((int)$payment['deal_id']);

        return $this->respondWithData([
            'payment' => $payment,
            'payouts' => $payouts,
        ]);
    }
}