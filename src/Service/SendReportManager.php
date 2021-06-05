<?php
namespace Oka\Notifier\ServerBundle\Service;

use Doctrine\Persistence\ObjectManager;
use Oka\Notifier\ServerBundle\Model\SendReportInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReportManager
{
    private $objectManager;
    private $class;
    
    /**
     * @var \Doctrine\Persistence\ObjectRepository
     */
    private $objectRepository;
    
    public function __construct(ObjectManager $objectManager, string $class)
    {
        $metadata = $objectManager->getClassMetadata($class);
        $this->objectManager = $objectManager;
        $this->class = $metadata->getName();
        
        $this->objectRepository = $objectManager->getRepository($this->class);
    }
    
    public function create(string $channel, array $paylaod = [], \DateTimeInterface $issuedAt = null): SendReportInterface
    {
        /** @var \Oka\Notifier\ServerBundle\Model\SendReportInterface $report */
        $report = new $this->class();
        $report->setChannel($channel);
        $report->setPaylaod($paylaod);
        
        if (null !== $issuedAt) {
            $report->setIssuedAt($issuedAt);
        }
        
        if (false === $this->objectManager->contains($report)) {
            $this->objectManager->persist($report);
        }
        
        $this->objectManager->flush();
        
        return $report;
    }
    
    public function find($id): SendReportInterface
    {
        return $this->objectRepository->find($id);
    }
    
    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array
    {
        return $this->objectRepository->findBy($criteria, $orderBy, $limit, $offset);
    }
}
