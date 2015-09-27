<?php

namespace Fidry\LoopBackApiBundle\Tests\Extractor;

use Fidry\LoopBackApiBundle\Extractor\PropertyExtractor;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\ExtractorPropertyExtractor
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyExtractor
     */
    private $propertyExtractor;

    protected function setUp()
    {
        $this->propertyExtractor = new PropertyExtractor();
    }

    /**
     * @cover ::getExplodedProperty
     * @dataProvider explodedPropertyProvider
     */
    public function testGetExplodedProperty($property, $expected)
    {
        $this->assertEquals($expected, $this->propertyExtractor->getExplodedProperty($property));
    }

    /**
     * @cover ::getResourceProperty
     * @dataProvider propertyProvider
     */
    public function testGetResourceProperty($property, $expected)
    {
        $this->assertEquals($expected, $this->propertyExtractor->getResourceProperty($property));
    }

    public function explodedPropertyProvider()
    {
        return [
            [
                'name',
                ['name']
            ],
            [
                'id',
                ['id']
            ],
            [
                'relatedDummy_name',
                [
                    'relatedDummy',
                    'name',
                ]
            ],
            [
                'relatedDummy.name',
                [
                    'relatedDummy',
                    'name',
                ]
            ],
            [
                'relatedDummy_id',
                [
                    'relatedDummy',
                    'id',
                ]
            ],
            [
                'relatedDummy.id',
                [
                    'relatedDummy',
                    'id',
                ]
            ],
            [
                'relatedDummy_user_name',
                [
                    'relatedDummy',
                    'user',
                    'name',
                ]
            ],
            [
                'relatedDummy.user.name',
                [
                    'relatedDummy',
                    'user',
                    'name',
                ]
            ],
            [
                'relatedDummy_user_id',
                [
                    'relatedDummy',
                    'user',
                    'id',
                ]
            ],
            [
                'relatedDummy.user.id',
                [
                    'relatedDummy',
                    'user',
                    'id',
                ]
            ],
        ];
    }

    public function propertyProvider()
    {
        return [
            [
                'name',
                'name'
            ],
            [
                'id',
                'id'
            ],
            [
                'relatedDummy_name',
                'name'
            ],
            [
                'relatedDummy.name',
                'name'
            ],
            [
                'relatedDummy_id',
                'id'
            ],
            [
                'relatedDummy.id',
                'id'
            ],
            [
                'relatedDummy_user_name',
                'name'
            ],
            [
                'relatedDummy.user.name',
                'name'
            ],
            [
                'relatedDummy_user_id',
                'id'
            ],
            [
                'relatedDummy.user.id',
                'id'
            ],
        ];
    }
}
