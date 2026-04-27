<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        PDO::class => function (ContainerInterface $c): PDO {
            $db = $c->get(SettingsInterface::class)->get('db');
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );
            return new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        },
        \App\Application\Services\JwtService::class => function (ContainerInterface $c) {
            return new \App\Application\Services\JwtService(
                $c->get(\App\Application\Settings\SettingsInterface::class)
            );
        },

        \App\Domain\User\UserRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\User\PdoUserRepository(
                $c->get(PDO::class)
            );
        },

        \App\Domain\Auth\RefreshTokenRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Auth\PdoRefreshTokenRepository(
                $c->get(PDO::class)
            );
        },
        \App\Application\Validation\RequestValidator::class => function () {
            return new \App\Application\Validation\RequestValidator();
        },
        \App\Application\Middleware\JwtAuthMiddleware::class => function (ContainerInterface $c) {
            return new \App\Application\Middleware\JwtAuthMiddleware(
                $c->get(\App\Application\Services\JwtService::class),
                $c->get(\App\Domain\User\UserRepository::class),
            );
        },
        \App\Domain\Brand\BrandRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Brand\PdoBrandRepository(
                $c->get(PDO::class)
            );
        },

        \App\Domain\Brand\BrandProductRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Brand\PdoBrandProductRepository(
                $c->get(PDO::class)
            );
        },
        \App\Domain\Influencer\InfluencerRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Influencer\PdoInfluencerRepository(
                $c->get(PDO::class)
            );
        },

        \App\Domain\Influencer\SocialAccountRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Influencer\PdoSocialAccountRepository(
                $c->get(PDO::class)
            );
        },
        \App\Domain\Affiliate\AffiliateRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Affiliate\PdoAffiliateRepository(
                $c->get(PDO::class)
            );
        },
        \App\Domain\Campaign\CampaignRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Campaign\PdoCampaignRepository(
                $c->get(PDO::class)
            );
        },

        \App\Domain\Campaign\DealRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Campaign\PdoDealRepository(
                $c->get(PDO::class)
            );
        },

        \App\Application\Services\PricingService::class => function (ContainerInterface $c) {
            return new \App\Application\Services\PricingService(
                $c->get(\App\Application\Settings\SettingsInterface::class)
            );
        },
        \App\Domain\Payment\PaymentRepository::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\Payment\PdoPaymentRepository(
                $c->get(PDO::class)
            );
        },
    ]);
};
