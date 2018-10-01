<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Cookie\Helper\Cookie as CookieHelper;

use Yireo\GoogleTagManager2\Config;

/**
 * Class ConfigTest
 *
 * @package Yireo\GoogleTagManager2\Test\Unit
 */
class ConfigTest extends TestCase
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @test
     * @covers Config::isEnabled
     */
    public function testIsEnabled()
    {
        $this->setScopeConfigValue('enabled', 1);
        $this->assertEquals($this->getTarget()->isEnabled(), true);

        $this->setScopeConfigValue('enabled', 0);
        $this->assertEquals($this->getTarget()->isEnabled(), false);
    }

    /**
     * @test
     * @covers Config::isDebug
     */
    public function testIsDebug()
    {
        $this->setScopeConfigValue('debug', 1);
        $this->assertEquals($this->getTarget()->isDebug(), true);

        $this->setScopeConfigValue('debug', 0);
        $this->assertEquals($this->getTarget()->isDebug(), false);
    }

    /**
     * @test
     * @covers Config::getId
     */
    public function testGetId()
    {
        $this->setScopeConfigValue('id', 42);
        $this->assertEquals($this->getTarget()->getId(), 42);

        $this->setScopeConfigValue('id', null);
        $this->assertEquals($this->getTarget()->getId(), null);
    }

    /**
     * @test
     * @covers Config::isMethodObserver
     */
    public function testIsMethodObserver()
    {
        $this->setScopeConfigValue('method', Config::METHOD_OBSERVER);
        $this->assertEquals($this->getTarget()->isMethodObserver(), true);

        $this->setScopeConfigValue('method', Config::METHOD_LAYOUT);
        $this->assertEquals($this->getTarget()->isMethodObserver(), false);
    }

    /**
     * @test
     * @covers Config::isMethodLayout
     */
    public function testIsMethodLayout()
    {
        $this->setScopeConfigValue('method', Config::METHOD_LAYOUT);
        $this->assertEquals($this->getTarget()->isMethodLayout(), true);

        $this->setScopeConfigValue('method', Config::METHOD_OBSERVER);
        $this->assertEquals($this->getTarget()->isMethodLayout(), false);
    }

    /**
     * @return Config
     */
    private function getTarget(): Config
    {
        $scopeConfig = $this->getScopeConfigMock();
        $cookieHelper = $this->getCookieHelperMock();
        $target = new Config($scopeConfig, $cookieHelper);
        return $target;
    }

    /**
     * @return ScopeConfigInterface
     */
    private function getScopeConfigMock(): ScopeConfigInterface
    {
        $this->scopeConfig = Mockery::mock(ScopeConfigInterface::class);

        foreach ($this->settings as $path => $value) {
            $this->scopeConfig->allows()->getValue($path, 'store')->andReturns($value);
        }

        return $this->scopeConfig;
    }

    /**
     * @return CookieHelper
     */
    private function getCookieHelperMock(): CookieHelper
    {
        $cookieHelper = Mockery::mock(CookieHelper::class);
        $cookieHelper->allows()->isCookieRestrictionModeEnabled()->andReturns(true);
        $cookieHelper->allows()->isUserNotAllowSaveCookie()->andReturns(false);

        return $cookieHelper;
    }

    /**
     * @param $path
     * @param $value
     * @param bool $prefix
     */
    private function setScopeConfigValue($path, $value, $prefix = true)
    {
        if ($prefix) {
            $path = 'googletagmanager2/settings/'.$path;
        }

        $this->settings[$path] = $value;
    }
}
