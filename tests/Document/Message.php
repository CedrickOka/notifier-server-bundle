<?php

namespace Oka\Notifier\ServerBundle\Tests\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\Notifier\ServerBundle\Model\Message as BaseMessage;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @MongoDB\Document(collection="message")
 */
class Message extends BaseMessage
{
    /**
     * @MongoDB\Id()
     *
     * @var string
     */
    protected $id;
}
