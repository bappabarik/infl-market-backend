<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name'  => 'inflmarket-api',
                    'path'  => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'db' => [
                    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                    'port' => $_ENV['DB_PORT'] ?? '3306',
                    'name' => $_ENV['DB_NAME'] ?? '',
                    'user' => $_ENV['DB_USER'] ?? 'root',
                    'pass' => $_ENV['DB_PASS'] ?? '',
                ],
                'jwt' => [
                    'secret'      => $_ENV['JWT_SECRET'] ?? '',
                    'access_ttl'  => (int)($_ENV['JWT_ACCESS_TTL'] ?? 900),
                    'refresh_ttl' => (int)($_ENV['JWT_REFRESH_TTL'] ?? 2592000),
                ],
                'cors' => [
                    'allowed_origins' => array_filter(array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? ''))),
                ],
                'platform' => [
                    'commission_percent' => (float)($_ENV['PLATFORM_COMMISSION_PERCENT'] ?? 15.00),
                ],
            ]);
        }
    ]);
};
