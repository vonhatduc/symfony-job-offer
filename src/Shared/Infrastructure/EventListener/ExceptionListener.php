<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Psr\Log\LoggerInterface;

/**
 * Global listener that transforms DomainExceptions into clean API responses
 */
#[AsEventListener(event: ExceptionEvent::class)]
final class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Unwrap Symfony Messenger exception if needed
        if ($exception instanceof HandlerFailedException && $exception->getPrevious() instanceof DomainException) {
            $exception = $exception->getPrevious();
        }

        if (!$exception instanceof DomainException) {
            return;
        }

        $response = new JsonResponse([
            'error' => 'Domain Error',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode() ?: 400
        ], 400);

        $event->setResponse($response);
    }
}
