<?php
namespace Oka\Notifier\ServerBundle\Test\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\Notifier\ServerBundle\Model\SendReport as BaseSendReport;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 * @MongoDB\Document(collection="send_report")
 */
class SendReport extends BaseSendReport
{
    /**
     * @MongoDB\Id()
     * 
     * @var string
     */
    protected $id;
}
