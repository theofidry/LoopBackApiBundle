<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\SchemaTool;
use Fidry\LoopBackApiBundle\Tests\Functional\Bundle\TestBundle\Entity\Dummy;
use PHPUnit_Framework_Assert as PHPUnit;
use Sanpi\Behatch\Json\Json;
use Sanpi\Behatch\Json\JsonInspector;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FeatureContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    /**
     * @var array
     */
    private $classes;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * Initializes context.
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $this->inspector = new JsonInspector('javascript');
    }

    /**
     * @Given there is :nb dummy objects
     *
     * @param int $nbr
     */
    public function thereIsDummyObjects($nbr)
    {
        for ($i = 1; $i <= $nbr; ++$i) {
            $dummy = new Dummy();
            $dummy->setName('Dummy #'.$i);
            $dummy->setAlias('Alias #'.($nbr - $i));

            $this->manager->persist($dummy);
        }

        $this->manager->flush();
    }

    /**
     * Checks that given JSON node is null
     *
     * @Then the JSON node :node should be null
     */
    public function theJsonNodeShouldBeNull($node)
    {
        $actual = $this->getJsonNodeValue($node);
        PHPUnit::assertNull($actual, sprintf('The node value is `%s`', json_encode($actual)));
    }

    /**
     * @Then the JSON node :node should be equal to the boolean :value
     */
    public function theJsonNodeShouldBeEmpty($node, $value)
    {
        $actual = $this->getJsonNodeValue($node);

        if ('false' === $value) {
            $value = false;
        } else {
            $value = (bool) $value;
        }

        PHPUnit::assertEquals($value, $actual, sprintf('The node value is `%s`', json_encode($actual)));
    }

    /**
     * @param string $node JSON node.
     *
     * @return mixed JSON node value.
     */
    private function getJsonNodeValue($node)
    {
        $json = $this->getJson();
        return $this->inspector->evaluate($json, $node);
    }

    /**
     * @return Json Get JSON content of the response.
     */
    private function getJson()
    {
        return new Json($this->getSession()->getPage()->getContent());
    }
}
