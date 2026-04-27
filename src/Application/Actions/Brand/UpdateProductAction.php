<?php
declare(strict_types=1);

namespace App\Application\Actions\Brand;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Brand\BrandProductRepository;
use App\Domain\Brand\BrandRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class UpdateProductAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
        private BrandProductRepository $products,
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

        $productId = (int)$this->resolveArg('id');

        if (!$this->products->belongsToBrand($productId, $brand->getId())) {
            throw new HttpBadRequestException($this->request, 'Product not found');
        }

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'title'         => v::optional(v::stringType()->length(2, 191)),
                'description'   => v::optional(v::stringType()->length(1, 5000)),
                'campaign_goal' => v::optional(v::stringType()->length(1, 5000)),
                'status'        => v::optional(v::in(['draft', 'active', 'archived'])),
            ]
        );

        $product = $this->products->update($productId, $data);

        return $this->respondWithData($product);
    }
}