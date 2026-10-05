<?php

namespace Oka\Notifier\ServerBundle\Tests\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CleanUpCommandTest extends KernelTestCase
{
    /**
     * @covers
     */
    public function testExecute(): void
    {
        self::bootKernel();

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schemaTool->updateSchema($metaData);

        /** @var \Oka\Notifier\ServerBundle\Service\SendReportManager $sendReportManager */
        $sendReportManager = static::getContainer()->get('oka_notifier_server.send_report_manager');
        $sendReportManager->create('firebase', []);
        sleep(3);

        $application = new Application(self::$kernel);
        $command = $application->find('oka:notifier:cleanup');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'classAlias' => 'sendReport',
            '--retention' => '1 second',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertEquals(0, count($sendReportManager->findBy([])));
    }
}
