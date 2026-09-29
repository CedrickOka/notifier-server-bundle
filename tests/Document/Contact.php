<?php

namespace Oka\Notifier\ServerBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Attribute as MongoDB;
use Oka\Notifier\ServerBundle\Model\Contact as BaseContact;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[MongoDB\Document(collection: 'contact')]
class Contact extends BaseContact
{
    /**
     * @var string
     */
    #[MongoDB\Id()]
    protected $id;
}
