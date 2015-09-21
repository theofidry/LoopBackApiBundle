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

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @ORM\Entity
 */
class RelatedDummy
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
    public $name;

    /**
     * @var float The dummy price.
     *
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"dummy"})
     */
    public $price;

    /**
     * @var RelatedDummy A related dummy.
     *
     * @ORM\OneToOne(targetEntity="AnotherDummy")
     * @Groups({"dummy"})
     */
    public $anotherDummy;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
