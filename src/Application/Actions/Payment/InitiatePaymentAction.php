<?php
declare(strict_types=1);

namespace App\Application\Actions\Payment;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Brand\BrandRepository;
use App\Domain\Campaign\DealRepository;
use App\Domain\Payment\PaymentRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class InitiatePaymentAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
        private DealRepository $deals,
        private PaymentRepository $payments,
        private RequestValidator $validator,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $brand = $this->brands->findByUserId($user->getId());

        if (!$brand) {
            throw new HttpNotFoundException($this->request, 'Brand profile not found');
        }

        $dealId = (int)$this->resolveArg('id');
        $deal = $this->deals->findById($dealId);

        if (!$deal) {
            throw new HttpNotFoundException($this->request, 'Deal not found');
        }

        if ((int)$deal['brand_id'] !== $brand->getId()) {
            throw new HttpForbiddenException($this->request, 'This deal does not belong to your campaign');
        }

        if (!in_array($deal['status'], ['approved', 'completed'])) {
            throw new HttpBadRequestException($this->request, 'Deal must be approved or completed before payment');
        }

        // Check if already paid
        $existing = $this->payments->findByDealId($dealId);
        if ($existing && $existing['status'] === 'success') {
            throw new HttpBadRequestException($this->request, 'This deal has already been paid');
        }

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'gateway'        => v::notEmpty()->in(['razorpay', 'stripe', 'manual']),
                'gateway_txn_id' => v::optional(v::stringType()->length(1, 191)),
            ]
        );

        // For manual payments, mark as success immediately
        $status = $data['gateway'] === 'manual' ? 'success' : 'initiated';

        $payment = $this->payments->create([
            'deal_id'        => $dealId,
            'brand_id'       => $brand->getId(),
            'gateway'        => $data['gateway'],
            'gateway_txn_id' => $data['gateway_txn_id'] ?? null,
            'amount'         => (float)$deal['total_amount'],
            'currency'       => 'INR',
            'status'         => $status,
            'paid_at'        => $status === 'success' ? date('Y-m-d H:i:s') : null,
        ]);

        // If payment is successful, create payout entries
        if ($status === 'success') {
            $this->payments->createPayouts((int)$payment['id'], $dealId, $deal);

            // Mark deal as completed if not already
            if ($deal['status'] !== 'completed') {
                $this->deals->updateStatus($dealId, 'completed');
            }
        }

        $payouts = $status === 'success'
            ? $this->payments->getPayoutsByDealId($dealId)
            : [];

        return $this->respondWithData([
            'payment' => $payment,
            'payouts' => $payouts,
        ], 201);
    }
}