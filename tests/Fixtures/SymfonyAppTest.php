<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class SymfonyAppTest extends KernelTestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp()
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testCompile()
    {
        $command = $this->application->find('help');
        $commandTester = new CommandTester($command);
        $commandTester->execute([], ['interactive' => false]);
        $this->assertTrue(true);
    }
}
