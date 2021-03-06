<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Tools\Console\Command;

use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\Tests\OrmFunctionalTestCase;
use LogicException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;
use function sprintf;

class ClearCacheResultCommandTest extends OrmFunctionalTestCase
{
    /** @var Application */
    private $application;

    /** @var ResultCommand */
    private $command;

    protected function setUp() : void
    {
        parent::setUp();

        $this->command = new ResultCommand();

        $this->application = new Application();
        $this->application->setHelperSet(new HelperSet(['em' => new EntityManagerHelper($this->em)]));
        $this->application->add($this->command);
    }

    public function dataInvalidCacheDrivers() : array
    {
        return [
            'apc' => ['Doctrine\Common\Cache\ApcCache', 'APC Cache'],
            'apcu' => ['Doctrine\Common\Cache\ApcuCache', 'APCu Cache'],
            'xcache' => ['Doctrine\Common\Cache\XcacheCache', 'XCache Cache'],
        ];
    }

    /** @dataProvider dataInvalidCacheDrivers */
    public function testCannotClearCacheWithInvalidDriver($driver, $name) : void
    {
        $this->em->getConfiguration()->setResultCacheImpl(new $driver());

        $command = $this->application->find('orm:clear-cache:result');

        $tester = new CommandTester($command);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('Cannot clear %s from Console', $name));

        $tester->execute(
            [
                'command' => $command->getName(),
            ],
            ['decorated' => false]
        );
    }
}
