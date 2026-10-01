<?php

namespace Oka\Notifier\ServerBundle\Service;

use Oka\Notifier\ServerBundle\Model\Contact;
use Oka\Notifier\ServerBundle\Model\ContactInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class ContactManager extends AbstractObjectManager
{
    public function create(string $channel, string $name, iterable $addresses): ContactInterface
    {
        /* @var \Oka\Notifier\ServerBundle\Model\ContactInterface $contact */
        if ((new \ReflectionClass($this->class))->isSubclassOf(Contact::class)) {
            $contact = new $this->class($channel, $name, $addresses);
        } else {
            $contact = new $this->class();
            $contact->setChannel($channel);
            $contact->setName($name);
            $contact->setAddresses($addresses);
        }

        if (false === $this->objectManager->contains($contact)) {
            $this->objectManager->persist($contact);
        }

        $this->objectManager->flush();

        return $contact;
    }
}
