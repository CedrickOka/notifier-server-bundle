<?php

namespace Oka\Notifier\ServerBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Attribute as MongoDB;
use Oka\Notifier\ServerBundle\Model\Message as BaseMessage;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[MongoDB\Document(collection: 'message')]
class Message extends BaseMessage
{
    /**
     * @var string
     */
    #[MongoDB\Id()]
    protected $id;
}
