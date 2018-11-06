<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map\Product;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Map\Product\Associate
 */
class AssociateTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product\Associate
     */
    private $model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->helper = $this->getMock(
            \Doofinder\Feed\Helper\Product::class,
            [],
            [],
            '',
            false
        );
        $this->helper->method('getAttributeText')->willReturn('sample value');

        $this->product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );
        $this->item->method('getContext')->willReturn($this->product);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Map\Product\Associate::class,
            [
                'helper' => $this->helper,
                'item' => $this->item,
            ]
        );
    }

    /**
     * Test get() method
     *
     * @param  string $key
     * @param  boolean $hasValue
     * @return void
     * @dataProvider providerTestGet
     */
    public function testGet($key, $hasValue)
    {
        $this->assertEquals(
            $hasValue ? 'sample value' : null,
            $this->model->get($key)
        );
    }

    /**
     * Data provider for testGet() test
     *
     * @return array
     */
    public function providerTestGet()
    {
        return [
            ['sample', true],
            ['df_id', false],
            ['name', false],
            ['description', false],
            ['price', false],
            ['image', false],
            ['type_id', false],
        ];
    }

    /**
     * Test get() method
     *
     * @return void
     */
    public function testGetUrlKey()
    {
        $this->product->method('isVisibleInSiteVisibility')->will(
            $this->onConsecutiveCalls(true, false)
        );
        $this->helper->method('getProductUrl')->with($this->product)
            ->willReturn('http://example.com/path/to/product');

        $this->assertEquals(
            'http://example.com/path/to/product',
            $this->model->get('url_key')
        );

        $this->assertEquals(
            null,
            $this->model->get('url_key')
        );
    }
}
