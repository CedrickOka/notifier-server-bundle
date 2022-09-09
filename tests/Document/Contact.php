<?php

namespace Oka\Notifier\ServerBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\Notifier\ServerBundle\Model\Contact as BaseContact;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @MongoDB\Document(collection="contact")
 */
class Contact extends BaseContact
{
    /**
     * @MongoDB\Id()
     *
     * @var string
     */
    protected $id;
}
