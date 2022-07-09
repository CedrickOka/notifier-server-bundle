<?php

namespace Oka\Notifier\ServerBundle\Service;

use Oka\Notifier\ServerBundle\Model\Address;
use Oka\Notifier\ServerBundle\Model\MessageInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class MessageManager extends AbstractObjectManager
{
    public function create(Address $from, Address $to, string $body, string $subject = null): MessageInterface
    {
        /** @var \Oka\Notifier\ServerBundle\Model\MessageInterface $message */
        $message = new $this->class();
        $message->setFrom($from);
        $message->setTo($to);
        $message->setBody($body);

        if (null !== $subject) {
            $message->setSubject($subject);
        }

        if (false === $this->objectManager->contains($message)) {
            $this->objectManager->persist($message);
        }

        $this->objectManager->flush();

        return $message;
    }

    public function find($id): MessageInterface
    {
        return $this->objectRepository->find($id);
    }
}
