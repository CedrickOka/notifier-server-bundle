<?php

namespace Oka\Notifier\ServerBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oka\Notifier\ServerBundle\Model\SendReport as BaseSendReport;

/**
 * @ORM\Entity()
 * @ORM\Table(name="send_report")
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReport extends BaseSendReport
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var string
     */
    protected $id;
}
