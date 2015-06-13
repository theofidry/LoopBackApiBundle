<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Test\TestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Dunglas\ApiBundle\Annotation\Iri;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dummy.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @ORM\Entity
 */
class Dummy
{
    /**
     * @var int The id.
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string The dummy name alias.
     *
     * @ORM\Column(nullable=true)
     * @Iri("https://schema.org/alternateName")
     */
    private $alias;

    /**
     * @var \DateTime A dummy date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime
     */
    public $dummyDate;

    /**
     * @var boolean
     *
     * @ORM\Column(nullable=true)
     */
    public $isEnabled;

    /**
     * @var string The dummy name.
     *
     * @ORM\Column
     * @Assert\NotBlank
     * @Iri("http://schema.org/name")
     */
    private $name;

    /**
     * @var int The dummy price.
     *
     * @ORM\Column
     */
    private $price;

    /**
     * @var RelatedDummy A related dummy.
     *
     * @ORM\ManyToOne(targetEntity="RelatedDummy")
     */
    public $relatedDummy;

    /**
     * @var ArrayCollection Several dummies.
     *
     * @ORM\ManyToMany(targetEntity="RelatedDummy")
     */
    public $relatedDummies;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->relatedDummies = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param \DateTime $dummyDate
     */
    public function setDummyDate(\DateTime $dummyDate)
    {
        $this->dummyDate = $dummyDate;
    }

    /**
     * @return \DateTime
     */
    public function getDummyDate()
    {
        return $this->dummyDate;
    }

    /**
     * Disables entity.
     *
     * @return $this
     */
    public function disable()
    {
        $this->isEnabled = false;

        return $this;
    }

    /**
     * Enables entity.
     *
     * @return $this
     */
    public function enable()
    {
        $this->isEnabled = true;

        return $this;
    }

    /**
     * Gets IsEnabled.
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Sets Price.
     *
     * @param int $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Gets Price.
     *
     * @return int|null
     */
    public function getPrice()
    {
        return $this->price;
    }
}
