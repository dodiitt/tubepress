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
 * @covers tubepress_wordpress_impl_listeners_wp_PublicActionsAndFilters
 */
class tubepress_test_wordpress_impl_listeners_wp_PublicActionsAndFiltersTest extends tubepress_test_TubePressUnitTest
{
    /**
     * @var tubepress_wordpress_impl_listeners_wp_PublicActionsAndFilters
     */
    private $_sut;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockStorageManager;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockShortcodeParser;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockHtmlGenerator;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockExecutionContext;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockMessageService;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockStringUtils;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockWordPressFunctionWrapper;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockTranslator;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockAjaxHandler;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockHttpRequestParams;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockEventDispatcher;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockEnvironment;

    public function onSetup()
    {
        $this->_mockWordPressFunctionWrapper = $this->mock(tubepress_wordpress_impl_wp_WpFunctions::_);
        $this->_mockExecutionContext         = $this->mock(tubepress_app_api_options_ContextInterface::_);
        $this->_mockMessageService           = $this->mock(tubepress_lib_api_translation_TranslatorInterface::_);
        $this->_mockHtmlGenerator            = $this->mock(tubepress_app_api_html_HtmlGeneratorInterface::_);
        $this->_mockShortcodeParser          = $this->mock(tubepress_app_api_shortcode_ParserInterface::_);
        $this->_mockStorageManager           = $this->mock(tubepress_app_api_options_PersistenceInterface::_);
        $this->_mockStringUtils              = $this->mock(tubepress_platform_api_util_StringUtilsInterface::_);
        $this->_mockEventDispatcher          = $this->mock(tubepress_lib_api_event_EventDispatcherInterface::_);
        $this->_mockAjaxHandler              = $this->mock(tubepress_lib_api_http_AjaxInterface::_);
        $this->_mockHttpRequestParams        = $this->mock(tubepress_lib_api_http_RequestParametersInterface::_);
        $this->_mockTranslator               = $this->mock(tubepress_lib_api_translation_TranslatorInterface::_);
        $this->_mockEnvironment              = $this->mock(tubepress_app_api_environment_EnvironmentInterface::_);

        $this->_sut = new tubepress_wordpress_impl_listeners_wp_PublicActionsAndFilters(

            $this->_mockWordPressFunctionWrapper,
            $this->_mockStringUtils,
            $this->_mockHtmlGenerator,
            $this->_mockAjaxHandler,
            $this->_mockHttpRequestParams,
            $this->_mockExecutionContext,
            $this->_mockStorageManager,
            $this->_mockShortcodeParser,
            $this->_mockTranslator,
            $this->_mockEventDispatcher,
            $this->_mockEnvironment
        );
    }

    public function testWidgetInitAction()
    {
        $widgetOps = array('classname' => 'widget_tubepress', 'description' => 'X');

        $this->_mockTranslator->shouldReceive('trans')->once()->with('Displays YouTube or Vimeo videos with TubePress')->andReturn('X');

        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_sidebar_widget')->once()->with('tubepress', 'TubePress', array($this->_sut, '__fireWidgetHtmlEvent'), $widgetOps);
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_widget_control')->once()->with('tubepress', 'TubePress', array($this->_sut, '__fireWidgetControlEvent'));

        $mockEvent = $this->mock('tubepress_lib_api_event_EventInterface');
        $this->_sut->onAction_widgets_init($mockEvent);
        $this->assertTrue(true);
    }

    public function testWpHeadExecuteInsideAdmin()
    {
        $this->_mockWordPressFunctionWrapper->shouldReceive('is_admin')->once()->andReturn(true);

        $mockEvent = $this->mock('tubepress_lib_api_event_EventInterface');
        $this->_sut->onAction_wp_head($mockEvent);
        $this->assertTrue(true);
    }

    public function testWpHeadExecuteOutsideAdmin()
    {

        $this->_mockHtmlGenerator->shouldReceive('getCSS')->once()->andReturn('hello there');
        $this->_mockHtmlGenerator->shouldReceive('getJS')->once()->andReturn('goodbye now');

        $this->_mockWordPressFunctionWrapper->shouldReceive('is_admin')->once()->andReturn(false);

        $this->expectOutputString('hello theregoodbye now');

        $mockEvent = $this->mock('tubepress_lib_api_event_EventInterface');
        $this->_sut->onAction_wp_head($mockEvent);

        $this->assertTrue(true);
    }

    public function testPrintWidgetControlHtml()
    {
        $this->_mockEventDispatcher->shouldReceive('dispatch')->once()
            ->with(tubepress_wordpress_api_Constants::EVENT_WIDGET_PRINT_CONTROLS);

        $this->_sut->__fireWidgetControlEvent();

        $this->assertTrue(true);
    }

    public function testPrintWidgetHtml()
    {
        $mockEvent = $this->mock('tubepress_lib_api_event_EventInterface');
        $this->_mockEventDispatcher->shouldReceive('newEventInstance')->once()->with(array(1))->andReturn($mockEvent);
        $this->_mockEventDispatcher->shouldReceive('dispatch')->once()->with(
            tubepress_wordpress_api_Constants::EVENT_WIDGET_PUBLIC_HTML, $mockEvent);

        $this->_sut->__fireWidgetHtmlEvent(array(1));

        $this->assertTrue(true);
    }

    public function testInitAction()
    {
        $mockSysJsUrl        = $this->mock('tubepress_platform_api_url_UrlInterface');
        $mockSystemStyleUrl  = $this->mock('tubepress_platform_api_url_UrlInterface');
        $mockUserStyleUrl    = $this->mock('tubepress_platform_api_url_UrlInterface');
        $mockSystemScriptUrl = $this->mock('tubepress_platform_api_url_UrlInterface');
        $mockUserScriptUrl   = $this->mock('tubepress_platform_api_url_UrlInterface');
        $mockBaseUrl         = $this->mock('tubepress_platform_api_url_UrlInterface');
        $mockUserUrl         = $this->mock('tubepress_platform_api_url_UrlInterface');

        $mockBaseUrl->shouldReceive('toString')->once()->andReturn('--base-url--');
        $mockUserUrl->shouldReceive('toString')->once()->andReturn('--user-url--');
        $mockSysJsUrl->shouldReceive('toString')->once()->andReturn('--base-url--/web/js/tubepress.js');
        $mockSystemStyleUrl->shouldReceive('toString')->once()->andReturn('--base-url--/web/something/system-style-url');
        $mockSystemScriptUrl->shouldReceive('toString')->once()->andReturn('--base-url--/web/something/system-script-url');
        $mockUserScriptUrl->shouldReceive('toString')->once()->andReturn('--user-url--/something/user-script-url');
        $mockUserStyleUrl->shouldReceive('toString')->once()->andReturn('--user-url--/something/user-style-url');

        $mockSysJsUrl->shouldReceive('isAbsolute')->once()->andReturn(false);
        $mockSystemStyleUrl->shouldReceive('isAbsolute')->once()->andReturn(false);
        $mockUserStyleUrl->shouldReceive('isAbsolute')->once()->andReturn(false);
        $mockSystemScriptUrl->shouldReceive('isAbsolute')->once()->andReturn(false);
        $mockUserScriptUrl->shouldReceive('isAbsolute')->once()->andReturn(false);

        $themeStyles  = array($mockSystemStyleUrl, $mockUserStyleUrl);
        $themeScripts = array($mockSysJsUrl, $mockSystemScriptUrl, $mockUserScriptUrl);
        $mockVersion = $this->mock('tubepress_platform_api_version_Version');
        $mockVersion->shouldReceive('__toString')->atLeast(1)->andReturn('tubepress-version');

        $this->_mockEnvironment->shouldReceive('getVersion')->once()->andReturn($mockVersion);
        $this->_mockEnvironment->shouldReceive('getBaseUrl')->once()->andReturn($mockBaseUrl);
        $this->_mockEnvironment->shouldReceive('getUserContentUrl')->once()->andReturn($mockUserUrl);

        $this->_mockHtmlGenerator->shouldReceive('getUrlsCSS')->once()->andReturn($themeStyles);
        $this->_mockHtmlGenerator->shouldReceive('getUrlsJS')->once()->andReturn($themeScripts);

        $this->_mockWordPressFunctionWrapper->shouldReceive('plugins_url')->once()->with('web/js/wordpress-ajax.js', 'tubepress/tubepress.php')->andReturn('<ajaxjs>');
        $this->_mockWordPressFunctionWrapper->shouldReceive('plugins_url')->once()->with('/web/js/tubepress.js', 'tubepress/tubepress.php')->andReturn('<<system-js-url>>');
        $this->_mockWordPressFunctionWrapper->shouldReceive('plugins_url')->once()->with('/web/something/system-style-url', 'tubepress/tubepress.php')->andReturn('<<system-style-url>>');
        $this->_mockWordPressFunctionWrapper->shouldReceive('plugins_url')->once()->with('/web/something/system-script-url', 'tubepress/tubepress.php')->andReturn('<<system-script-url>>');
        $this->_mockWordPressFunctionWrapper->shouldReceive('content_url')->once()->with('tubepress-content/something/user-style-url')->andReturn('<<user-style-url>>');
        $this->_mockWordPressFunctionWrapper->shouldReceive('content_url')->once()->with('tubepress-content/something/user-script-url')->andReturn('<<user-script-url>>');

        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_style')->once()->with('tubepress-theme-0', '<<system-style-url>>', array(), 'tubepress-version');
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_style')->once()->with('tubepress-theme-1', '<<user-style-url>>', array(), 'tubepress-version');
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_enqueue_style')->once()->with('tubepress-theme-0');
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_enqueue_style')->once()->with('tubepress-theme-1');

        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_script')->once()->with('tubepress', '<<system-js-url>>', array(), 'tubepress-version');
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_script')->once()->with('tubepress_ajax', '<ajaxjs>', array('tubepress'), 'tubepress-version');
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_script')->once()->with('tubepress-theme-1', '<<system-script-url>>', array('tubepress'), 'tubepress-version');
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_register_script')->once()->with('tubepress-theme-2', '<<user-script-url>>', array('tubepress'), 'tubepress-version');
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_enqueue_script')->once()->with('tubepress', false, array(), false, false);
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_enqueue_script')->once()->with('tubepress-theme-1', false, array(), false, false);
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_enqueue_script')->once()->with('tubepress-theme-2', false, array(), false, false);
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_enqueue_script')->once()->with('jquery', false, array(), false, false);
        $this->_mockWordPressFunctionWrapper->shouldReceive('wp_enqueue_script')->once()->with('tubepress_ajax', false, array(), false, false);

        $this->_mockWordPressFunctionWrapper->shouldReceive('is_admin')->once()->andReturn(false);

        $this->_mockStringUtils->shouldReceive('endsWith')->once()->with('<<system-js-url>>', '/web/js/tubepress.js')->andReturn(true);
        $this->_mockStringUtils->shouldReceive('endsWith')->once()->with('<<system-script-url>>', '/web/js/tubepress.js')->andReturn(false);
        $this->_mockStringUtils->shouldReceive('endsWith')->once()->with('<<user-script-url>>', '/web/js/tubepress.js')->andReturn(false);
        $this->_mockStringUtils->shouldReceive('startsWith')->once()->with('--base-url--/web/js/tubepress.js', '--base-url--/web/')->andReturn(true);
        $this->_mockStringUtils->shouldReceive('startsWith')->once()->with('--base-url--/web/something/system-style-url', '--base-url--/web/')->andReturn(true);
        $this->_mockStringUtils->shouldReceive('startsWith')->once()->with('--base-url--/web/something/system-script-url', '--base-url--/web/')->andReturn(true);
        $this->_mockStringUtils->shouldReceive('startsWith')->once()->with('--user-url--/something/user-style-url', '--base-url--/web/')->andReturn(false);
        $this->_mockStringUtils->shouldReceive('startsWith')->once()->with('--user-url--/something/user-style-url', '--user-url--/')->andReturn(true);
        $this->_mockStringUtils->shouldReceive('startsWith')->once()->with('--user-url--/something/user-script-url', '--base-url--/web/')->andReturn(false);
        $this->_mockStringUtils->shouldReceive('startsWith')->once()->with('--user-url--/something/user-script-url', '--user-url--/')->andReturn(true);

        $mockEvent = $this->mock('tubepress_lib_api_event_EventInterface');
        $this->_sut->onAction_init($mockEvent);
        $this->assertTrue(true);
    }

    public function testFilterContent()
    {
        $this->_mockStorageManager->shouldReceive('fetch')->once()->with(tubepress_app_api_options_Names::SHORTCODE_KEYWORD)->andReturn('trigger word');

        $this->_mockShortcodeParser->shouldReceive('somethingToParse')->times(2)->with('the content', 'trigger word')->andReturn(true);
        $this->_mockShortcodeParser->shouldReceive('somethingToParse')->times(2)->with('html for shortcode', 'trigger word')->andReturn(true, false);
        $this->_mockShortcodeParser->shouldReceive('getLastShortcodeUsed')->times(4)->andReturn('<current shortcode>');
        $this->_mockShortcodeParser->shouldReceive('parse')->times(2);

        $this->_mockHtmlGenerator->shouldReceive('getHtml')->once()->andReturn('html for shortcode');
        $this->_mockHtmlGenerator->shouldReceive('getHtml')->once()->andReturn('html for shortcode');

        $this->_mockExecutionContext->shouldReceive('setEphemeralOptions')->twice()->with(array());

        $mockEvent = $this->mock('tubepress_lib_api_event_EventInterface');
        $mockEvent->shouldReceive('getSubject')->once()->andReturn('the content');
        $mockEvent->shouldReceive('setSubject')->once()->with('html for shortcode');

        $this->_mockStringUtils->shouldReceive('replaceFirst')->once()->with('<current shortcode>', 'html for shortcode', 'the content')->andReturn('html for shortcode');
        $this->_mockStringUtils->shouldReceive('replaceFirst')->once()->with('<current shortcode>', 'html for shortcode', 'html for shortcode')->andReturn('html for shortcode');
        $this->_mockStringUtils->shouldReceive('removeEmptyLines')->twice()->with('html for shortcode')->andReturn('html for shortcode');

        $this->_sut->onFilter_the_content($mockEvent);

        $this->assertTrue(true);
    }
}
