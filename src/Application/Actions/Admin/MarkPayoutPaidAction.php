<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Action;
use App\Domain\Payment\PaymentRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class MarkPayoutPaidAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PaymentRepository $payments,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $payoutId = (int)$this->resolveArg('id');
        $payout = $this->payments->findPayoutById($payoutId);

        if (!$payout) {
            throw new HttpNotFoundException($this->request, 'Payout not found');
        }

        $updated = $this->payments->updatePayoutStatus($payoutId, 'paid');

        return $this->respondWithData($updated);
    }
}