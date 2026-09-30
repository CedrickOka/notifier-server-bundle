<?php

namespace Oka\Notifier\ServerBundle\Service;

use Oka\Notifier\ServerBundle\Model\SendReport;
use Oka\Notifier\ServerBundle\Model\SendReportInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReportManager extends AbstractObjectManager
{
    public function create(string $channel, array $payload = []): SendReportInterface
    {
        /* @var \Oka\Notifier\ServerBundle\Model\SendReportInterface $report */
        if ((new \ReflectionClass($this->class))->isSubclassOf(SendReport::class)) {
            $report = new $this->class($channel, $payload);
        } else {
            $report = new $this->class();
            $report->setChannel($channel);
            $report->setPayload($payload);
        }

        if (false === $this->objectManager->contains($report)) {
            $this->objectManager->persist($report);
        }

        $this->objectManager->flush();

        return $report;
    }
}
