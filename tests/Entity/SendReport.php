<?php

namespace Oka\Notifier\ServerBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oka\Notifier\ServerBundle\Model\SendReport as BaseSendReport;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
#[ORM\Entity()]
#[ORM\Table(name: 'send_report')]
class SendReport extends BaseSendReport
{
    /**
     * @var string
     */
    #[ORM\Id()]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;
}
