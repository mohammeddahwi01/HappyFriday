<?php

namespace Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log\Column\Type;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Ui\Component\Listing\Log\Column\Type\Options
 */
class OptionsTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $logger;

    /**
     * @var \Doofinder\Feed\Ui\Component\Listing\Log\Column\Type\Options
     */
    private $options;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->getMock(
            \Doofinder\Feed\Logger\Feed::class,
            [],
            [],
            '',
            false
        );
        $this->logger->method('getLevelOptions')->willReturn([
            100 => 'DEBUG',
            200 => 'INFO',
            300 => 'WARNING',
            400 => 'ERROR',
        ]);

        $this->options = $this->objectManager->getObject(
            \Doofinder\Feed\Ui\Component\Listing\Log\Column\Type\Options::class,
            [
                'logger' => $this->logger,
            ]
        );
    }

    /**
     * Test toOptionArray() method
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $expected = [
            ['label' => 'debug', 'value' => 'debug'],
            ['label' => 'info', 'value' => 'info'],
            ['label' => 'warning', 'value' => 'warning'],
            ['label' => 'error', 'value' => 'error'],
        ];

        $this->assertEquals(
            $expected,
            $this->options->toOptionArray()
        );
    }
}
