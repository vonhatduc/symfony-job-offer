<?php

declare(strict_types=1);

namespace App\JobManagement\Application\EventListener;

use App\JobManagement\Domain\Event\JobAppliedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handles job application events
 */
#[AsMessageHandler]
final class JobAppliedListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(JobAppliedEvent $event): void
    {
        $this->logger->info(sprintf(
            'User %d applied to job %d',
            $event->userId,
            $event->jobOfferId
        ));
    }
}
