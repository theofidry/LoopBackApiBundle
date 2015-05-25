<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AppKernel.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Dunglas\ApiBundle\Tests\Behat\TestBundle\TestBundle(),
            new Dunglas\ApiBundle\Tests\Behat\TestBundle\TestBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Fidry\LoopBackApiBundle\AppBundle\AppBundle(),
            new Fidry\LoopBackApiBundle\LoopBackApiBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
