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

use Dunglas\ApiBundle\Annotation\Iri;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Related dummy.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @ORM\Entity
 * @Iri("https://schema.org/Product")
 */
class RelatedDummy
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int The age.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $age;

    /**
     * @ORM\Column
     * @Groups({"barcelona", "chicago"})
     */
    protected $symfony = 'symfony';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param $symfony
     */
    public function setSymfony($symfony)
    {
        $this->symfony = $symfony;
    }

    /**
     * @return string
     */
    public function getSymfony()
    {
        return $this->symfony;
    }
}
