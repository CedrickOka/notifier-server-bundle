<?php

namespace Oka\Notifier\ServerBundle\Service;

use Doctrine\Persistence\ObjectManager;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class AbstractObjectManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Doctrine\Persistence\ObjectRepository
     */
    protected $objectRepository;

    /**
     * @var string
     */
    protected $class;

    public function __construct(ObjectManager $objectManager, string $class)
    {
        $metadata = $objectManager->getClassMetadata($class);

        $this->class = $metadata->getName();
        $this->objectManager = $objectManager;
        $this->objectRepository = $objectManager->getRepository($this->class);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->objectRepository->findBy($criteria, $orderBy, $limit, $offset);
    }
}
