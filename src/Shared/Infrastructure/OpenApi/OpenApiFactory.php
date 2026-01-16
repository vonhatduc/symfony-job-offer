<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $components = $openApi->getComponents();
        $schemas = $components->getSecuritySchemes() ?? new \ArrayObject();

        $schemas['bearerAuth'] = new Model\SecurityScheme(
            type: 'http',
            scheme: 'bearer',
            bearerFormat: 'JWT',
            description: 'Enter your JWT token ONLY (without "Bearer " prefix)'
        );

        // Apply security requirement globally
        $openApi = $openApi
            ->withComponents($components->withSecuritySchemes($schemas))
            ->withSecurity([['bearerAuth' => []]]);

        // Explicitly apply to each operation to be safe with API Platform 3
        $paths = $openApi->getPaths();
        foreach ($paths->getPaths() as $path => $pathItem) {
            foreach (['get', 'post', 'put', 'delete', 'patch'] as $method) {
                $operation = $pathItem->{'get'.ucfirst($method)}();
                if ($operation) {
                    $pathItem = $pathItem->{'with'.ucfirst($method)}(
                        $operation->withSecurity([['bearerAuth' => []]])
                    );
                }
            }
            $openApi->getPaths()->addPath($path, $pathItem);
        }

        return $openApi;
    }
}
