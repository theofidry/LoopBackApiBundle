<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Tests\Functional\Bundle\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
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
     * @var \DateTime A dummy date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"dummy"})
     */
    public $dummyDate;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"dummy"})
     */
    public $enabled;

    /**
     * @var string The dummy name.
     *
     * @ORM\Column(nullable=true)
     * @Groups({"dummy"})
     */
    private $name;

    /**
     * @var float The dummy price.
     *
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"dummy"})
     */
    private $price;

    /**
     * @var RelatedDummy A related dummy.
     *
     * @ORM\OneToOne(targetEntity="RelatedDummy")
     * @Groups({"dummy"})
     */
    public $relatedDummy;

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
        $this->enabled = false;

        return $this;
    }

    /**
     * Enables entity.
     *
     * @return $this
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Gets IsEnabled.
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Sets Price.
     *
     * @param float $price
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
     * @return float|null
     */
    public function getPrice()
    {
        return $this->price;
    }
}
