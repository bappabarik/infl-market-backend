<?php
declare(strict_types=1);

namespace App\Application\Actions\Brand;

use App\Application\Actions\Action;
use App\Domain\Brand\BrandProductRepository;
use App\Domain\Brand\BrandRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class DeleteProductAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
        private BrandProductRepository $products,
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

        $productId = (int)$this->resolveArg('id');

        if (!$this->products->belongsToBrand($productId, $brand->getId())) {
            throw new HttpBadRequestException($this->request, 'Product not found');
        }

        $this->products->delete($productId);

        return $this->respondWithData(['message' => 'Product archived']);
    }
}