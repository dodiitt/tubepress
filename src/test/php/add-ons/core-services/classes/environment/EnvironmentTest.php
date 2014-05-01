<?php
/**
 * Copyright 2006 - 2014 TubePress LLC (http://tubepress.com)
 *
 * This file is part of TubePress (http://tubepress.com)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * @covers tubepress_addons_coreservices_impl_environment_Environment<extended>
 */
class tubepress_test_addons_coreservices_impl_environment_EnvironmentTest extends tubepress_test_TubePressUnitTest
{
    /**
     * @var tubepress_addons_coreservices_impl_environment_Environment
     */
    private $_sut;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockUrlFactory;

    public function onSetup()
    {
        $this->_mockUrlFactory = ehough_mockery_Mockery::mock(tubepress_api_url_UrlFactoryInterface::_);
        $this->_sut            = new tubepress_addons_coreservices_impl_environment_Environment($this->_mockUrlFactory);
    }

    public function testVersion()
    {
        $latest = tubepress_api_version_Version::parse('9.9.9');
        $current = $this->_sut->getVersion();
        $this->assertTrue($current instanceof tubepress_api_version_Version);
        $this->assertTrue($latest->compareTo($current) === 0, "Expected $latest but got $current");
    }

    public function testIsProTrue()
    {
        $this->_sut->markAsPro();
        $this->assertTrue($this->_sut->isPro());
    }

    public function testIsWordPressTrue()
    {
        $this->_sut->setWpFunctionsInterface(ehough_mockery_Mockery::mock(tubepress_addons_wordpress_spi_WpFunctionsInterface::_));
        $this->assertTrue($this->_sut->isWordPress());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid argument to tubepress_addons_coreservices_impl_environment_Environment::setWpFunctionsInterface
     */
    public function testBadWpInterface()
    {
        $this->_sut->setWpFunctionsInterface(new stdClass());
    }

    public function testIsProFalse()
    {
        $this->assertFalse($this->_sut->isPro());
    }

    public function testIsWordPressFalse()
    {
        $this->assertFalse($this->_sut->isWordPress());
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserContentDirDefined()
    {
        define('TUBEPRESS_CONTENT_DIRECTORY', 'boo//////');

        $this->assertEquals('boo', $this->_sut->getUserContentDirectory());
    }

    public function testUserContentDirNotDefined()
    {
        $this->assertEquals(TUBEPRESS_ROOT . '/tubepress-content', $this->_sut->getUserContentDirectory());
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserContentDirWp1()
    {
        define('ABSPATH', 'blue/');
        $mockWp  = ehough_mockery_Mockery::mock(tubepress_addons_wordpress_spi_WpFunctionsInterface::_);
        $this->_sut->setWpFunctionsInterface($mockWp);

        $this->assertEquals('blue/wp-content/tubepress-content', $this->_sut->getUserContentDirectory());
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserContentDirWp2()
    {
        define('WP_CONTENT_DIR', 'bob');
        $mockWp  = ehough_mockery_Mockery::mock(tubepress_addons_wordpress_spi_WpFunctionsInterface::_);
        $this->_sut->setWpFunctionsInterface($mockWp);

        $this->assertEquals('bob/tubepress-content', $this->_sut->getUserContentDirectory());
    }

    public function testDetectUserContentUrlNonWp()
    {
        $mockUrl        = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $mockContentUrl = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $mockContentUrl->shouldReceive('toString')->once()->andReturn('abc');
        $this->_mockUrlFactory->shouldReceive('fromString')->once()->with('yellow')->andReturn($mockContentUrl);
        $this->_mockUrlFactory->shouldReceive('fromString')->once()->with('abc/tubepress-content')->andReturn($mockUrl);
        $this->_sut->setBaseUrl('yellow');
        $result = $this->_sut->getUserContentUrl();
        $this->assertSame($mockUrl, $result);
    }

    public function testDetectUserContentUrlWp()
    {
        $mockUrl = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $mockWp  = ehough_mockery_Mockery::mock(tubepress_addons_wordpress_spi_WpFunctionsInterface::_);
        $mockWp->shouldReceive('content_url')->once()->andReturn('xyz');
        $this->_sut->setWpFunctionsInterface($mockWp);
        $this->_mockUrlFactory->shouldReceive('fromString')->once()->with('xyz/tubepress-content')->andReturn($mockUrl);
        $result = $this->_sut->getUserContentUrl();
        $this->assertSame($mockUrl, $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetUserContentUrlFromDefineBad()
    {
        define('TUBEPRESS_CONTENT_URL', 'yoyo');

        $this->_mockUrlFactory->shouldReceive('fromString')->once()->with('yoyo')->andThrow(new InvalidArgumentException());
        $result = $this->_sut->getUserContentUrl();

        $this->assertNull($result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetUserContentUrlFromDefineGood()
    {
        define('TUBEPRESS_CONTENT_URL', 'yoyo');

        $mockUrl = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $this->_mockUrlFactory->shouldReceive('fromString')->once()->with('yoyo')->andReturn($mockUrl);
        $result = $this->_sut->getUserContentUrl();

        $this->assertSame($mockUrl, $result);
    }

    public function testDefaults()
    {
        $this->assertNull($this->_sut->getBaseUrl());
        $this->assertNull($this->_sut->getUserContentUrl());
    }

    public function testSetUserContentUrlAsRealUrl()
    {
        $mockUrl = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $this->_sut->setUserContentUrl($mockUrl);
        $this->assertSame($mockUrl, $this->_sut->getUserContentUrl());
    }

    public function testSetUserContentUrlAsString()
    {
        $mockUrl = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $this->_mockUrlFactory->shouldReceive('fromString')->once()->with('abc')->andReturn($mockUrl);
        $this->_sut->setUserContentUrl('abc');
        $this->assertSame($mockUrl, $this->_sut->getUserContentUrl());
    }

    public function testSetBaseUrlAsRealUrl()
    {
        $mockUrl = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $this->_sut->setBaseUrl($mockUrl);
        $this->assertSame($mockUrl, $this->_sut->getBaseUrl());
    }

    public function testSetBaseUrlAsString()
    {
        $mockUrl = ehough_mockery_Mockery::mock('tubepress_api_url_UrlInterface');
        $this->_mockUrlFactory->shouldReceive('fromString')->once()->with('abc')->andReturn($mockUrl);
        $this->_sut->setBaseUrl('abc');
        $this->assertSame($mockUrl, $this->_sut->getBaseUrl());
    }
}
