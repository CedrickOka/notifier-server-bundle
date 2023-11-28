<?php

namespace Oka\Notifier\ServerBundle\Service;

use Oka\Notifier\ServerBundle\Model\SendReportInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReportManager extends AbstractObjectManager
{
    public function create(string $channel, array $payload = [], \DateTimeInterface $issuedAt = null): SendReportInterface
    {
        /** @var \Oka\Notifier\ServerBundle\Model\SendReportInterface $report */
        $report = new $this->class();
        $report->setChannel($channel);
        $report->setPayload($payload);

        if (null !== $issuedAt) {
            $report->setIssuedAt($issuedAt);
        }

        if (false === $this->objectManager->contains($report)) {
            $this->objectManager->persist($report);
        }

        $this->objectManager->flush();

        return $report;
    }

    public function find($id): SendReportInterface
    {
        return $this->objectRepository->find($id);
    }
}
