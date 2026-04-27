<?php
declare(strict_types=1);

namespace App\Application\Actions\Brand;

use App\Application\Actions\Action;
use App\Domain\Brand\BrandRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class GetMyBrandAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
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

        return $this->respondWithData($brand->jsonSerialize());
    }
}