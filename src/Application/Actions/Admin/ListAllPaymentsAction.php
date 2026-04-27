<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Action;
use App\Domain\Payment\PaymentRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListAllPaymentsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PaymentRepository $payments,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $params = $this->request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $payments = $this->payments->getAllPayments($limit, $offset);
        $total = $this->payments->countAllPayments();

        return $this->respondWithData([
            'payments'   => $payments,
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => (int)ceil($total / $limit),
            ],
        ]);
    }
}