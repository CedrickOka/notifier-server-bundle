<?php

namespace Oka\Notifier\ServerBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Oka\Notifier\ServerBundle\Service\MessageManager;
use Oka\Notifier\ServerBundle\Service\SendReportManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUpCommand extends Command
{
    public function __construct(private ?MessageManager $messageManager = null, private ?SendReportManager $sendReportManager = null)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Clean up old object.')
            ->setHelp(<<<EOF
The <info>oka:notifier:cleanup</info> command activates a user (so they will be able to log in):

  <info>php %command.full_name% admin</info>
EOF
            )
            ->addArgument('classAlias', InputArgument::REQUIRED, '', null, ['message', 'sendReport'])
            ->addOption('retention', 'r', InputOption::VALUE_REQUIRED, '90 days');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (false === in_array($input->getArgument('classAlias'), ['message', 'sendReport'])) {
            return Command::INVALID;
        }

        switch (true) {
            case 'message' === $input->getArgument('classAlias') && null !== $this->messageManager:
                $className = $this->messageManager->getClassName();
                $objectManager = $this->messageManager->getObjectManager();
                break;

            case 'sendReport' === $input->getArgument('classAlias') && null !== $this->sendReportManager:
                $className = $this->sendReportManager->getClassName();
                $objectManager = $this->sendReportManager->getObjectManager();
                break;

            default:
                return Command::FAILURE;
        }

        $datetime = (new \DateTime())->sub(\DateInterval::createFromDateString($input->getOption('retention')));

        if ($objectManager instanceof EntityManagerInterface) {
            $objectManager->createQueryBuilder()
                        ->delete($className, 'o')
                        ->where('o.issuedAt <= :datetime')
                        ->setParameter('datetime', $datetime)
                        ->getQuery()
                        ->execute();
        } else {
            $objectManager->createQueryBuilder()
                        ->remove($className)
                        ->field('issuedAt')->lte($datetime)
                        ->getQuery()
                        ->execute();
        }

        return Command::SUCCESS;
    }
}
