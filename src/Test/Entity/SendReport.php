<?php

namespace Oka\Notifier\ServerBundle\Test\Document;

use Doctrine\ORM\Mapping as ORM;
use Oka\Notifier\ServerBundle\Model\SendReport as BaseSendReport;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReport extends BaseSendReport
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="int")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var string
     */
    protected $id;
}
