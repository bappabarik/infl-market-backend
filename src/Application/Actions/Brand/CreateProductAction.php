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
use Slim\Exception\HttpNotFoundException;

class CreateProductAction extends Action
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

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'title'         => v::notEmpty()->stringType()->length(2, 191),
                'description'   => v::optional(v::stringType()->length(1, 5000)),
                'campaign_goal' => v::optional(v::stringType()->length(1, 5000)),
            ]
        );

        $product = $this->products->create(
            $brand->getId(),
            $data['title'],
            $data['description'] ?? null,
            $data['campaign_goal'] ?? null,
        );

        return $this->respondWithData($product, 201);
    }
}