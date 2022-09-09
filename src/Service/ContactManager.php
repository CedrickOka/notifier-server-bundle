<?php

namespace Oka\Notifier\ServerBundle\Service;

use Oka\Notifier\ServerBundle\Model\ContactInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class ContactManager extends AbstractObjectManager
{
    public function create(string $channel, string $name, iterable $addresses): ContactInterface
    {
        /** @var \Oka\Notifier\ServerBundle\Model\ContactInterface $contact */
        $contact = new $this->class();
        $contact->setChannel($channel);
        $contact->setName($name);
        $contact->setAddresses($addresses);

        if (false === $this->objectManager->contains($contact)) {
            $this->objectManager->persist($contact);
        }

        $this->objectManager->flush();

        return $contact;
    }

    public function find($id): ContactInterface
    {
        return $this->objectRepository->find($id);
    }

    public function save(ContactInterface $contact): void
    {
        if (false === $this->objectManager->contains($contact)) {
            $this->objectManager->persist($contact);
        }

        $this->objectManager->flush();
    }

    public function remove(ContactInterface $contact): void
    {
        $this->objectManager->remove($contact);
        $this->objectManager->flush();
    }
}
