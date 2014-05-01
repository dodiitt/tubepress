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
 * A service locator for kernel services.
 */
class tubepress_impl_patterns_sl_ServiceLocator
{
    /**
     * @var ehough_iconic_ContainerInterface This is a special member that is a reference to the core IOC service.
     *            It lets us perform lazy lookups for core services.
     */
    private static $_iocContainer;

    /**
     * @return tubepress_spi_http_AjaxHandler
     */
    public static function getAjaxHandler()
    {
        return self::getService(tubepress_spi_http_AjaxHandler::_);
    }

    /**
     * @return ehough_stash_interfaces_PoolInterface The cache service.
     */
    public static function getCacheService()
    {
        return self::getService('ehough_stash_interfaces_PoolInterface');
    }

    /**
     * @return tubepress_spi_html_CssAndJsHtmlGeneratorInterface The head HTML generator.
     */
    public static function getCssAndJsHtmlGenerator()
    {
        return self::getService(tubepress_spi_html_CssAndJsHtmlGeneratorInterface::_);
    }

    /**
     * @return tubepress_spi_embedded_EmbeddedHtmlGenerator The embedded HTML generator.
     */
    public static function getEmbeddedHtmlGenerator()
    {
        return self::getService(tubepress_spi_embedded_EmbeddedHtmlGenerator::_);
    }

    /**
     * @return tubepress_api_event_EventDispatcherInterface The event dispatcher.
     */
    public static function getEventDispatcher()
    {
        return self::getService(tubepress_api_event_EventDispatcherInterface::_);
    }

    /**
     * @return tubepress_spi_context_ExecutionContext The execution context.
     */
    public static function getExecutionContext()
    {
        return self::getService(tubepress_spi_context_ExecutionContext::_);
    }

    /**
     * @return ehough_filesystem_FilesystemInterface The filesystem service.
     */
    public static function getFileSystem()
    {
        return self::getService('ehough_filesystem_FilesystemInterface');
    }

    /**
     * @return ehough_finder_FinderFactoryInterface The finder factory.
     */
    public static function getFileSystemFinderFactory()
    {
        return self::getService('ehough_finder_FinderFactoryInterface');
    }

    /**
     * @return tubepress_spi_http_HttpClientInterface The HTTP client.
     */
    public static function getHttpClient()
    {
        return self::getService(tubepress_spi_http_HttpClientInterface::_);
    }

    /**
     * @return tubepress_spi_http_HttpRequestParameterService The HTTP request parameter service.
     */
    public static function getHttpRequestParameterService()
    {
        return self::getService(tubepress_spi_http_HttpRequestParameterService::_);
    }

    /**
     * @return tubepress_spi_http_ResponseCodeHandler The HTTP response code handler.
     */
    public static function getHttpResponseCodeHandler()
    {
        return self::getService(tubepress_spi_http_ResponseCodeHandler::_);
    }

    /**
     * @return tubepress_spi_message_MessageService The message service.
     */
    public static function getMessageService()
    {
        return self::getService(tubepress_spi_message_MessageService::_);
    }

    /**
     * @return tubepress_spi_options_ui_OptionsPageInterface The UI form handler.
     */
    public static function getOptionsPage()
    {
        return self::getService('tubepress_spi_options_ui_OptionsPageInterface');
    }

    /**
     * @return tubepress_spi_options_OptionProvider The options provider.
     */
    public static function getOptionProvider()
    {
        return self::getService(tubepress_spi_options_OptionProvider::_);
    }

    /**
     * @return tubepress_spi_options_StorageManager The option storage manager.
     */
    public static function getOptionStorageManager()
    {
        return self::getService(tubepress_spi_options_StorageManager::_);
    }

    /**
     * @return tubepress_spi_player_PlayerHtmlGenerator The player HTML generator.
     */
    public static function getPlayerHtmlGenerator()
    {
        return self::getService(tubepress_spi_player_PlayerHtmlGenerator::_);
    }

    /**
     * @return tubepress_spi_shortcode_ShortcodeHtmlGenerator The shortcode HTML generator.
     */
    public static function getShortcodeHtmlGenerator()
    {
        return self::getService(tubepress_spi_shortcode_ShortcodeHtmlGenerator::_);
    }

    /**
     * @return tubepress_spi_shortcode_ShortcodeParser The shortcode parser.
     */
    public static function getShortcodeParser()
    {
        return self::getService(tubepress_spi_shortcode_ShortcodeParser::_);
    }

    /**
     * @return ehough_contemplate_api_TemplateBuilder The template builder.
     */
    public static function getTemplateBuilder()
    {
        return self::getService('ehough_contemplate_api_TemplateBuilder');
    }

    /**
     * @return tubepress_spi_theme_ThemeFinderInterface
     */
    public static function getThemeFinder()
    {
        return self::getService(tubepress_spi_theme_ThemeFinderInterface::_);
    }

    /**
     * @return tubepress_spi_theme_ThemeHandlerInterface The theme handler.
     */
    public static function getThemeHandler()
    {
        return self::getService(tubepress_spi_theme_ThemeHandlerInterface::_);
    }

    /**
     * @return tubepress_spi_url_UrlFactoryInterface The URL factory.
     */
    public static function getUrlFactoryInterface()
    {
        return self::getService(tubepress_spi_url_UrlFactoryInterface::_);
    }

    /**
     * @return tubepress_spi_collector_VideoCollector The video collector.
     */
    public static function getVideoCollector()
    {
        return self::getService(tubepress_spi_collector_VideoCollector::_);
    }

    /**
     * @param ehough_iconic_ContainerInterface $container The core IOC container.
     */
    public static function setBackingIconicContainer(ehough_iconic_ContainerInterface $container)
    {
        self::$_iocContainer = $container;
    }

    /**
     * Retrieve an arbitrary service.
     *
     * @param string $serviceId The ID of the service to retrieve.
     *
     * @return object The service instance.
     */
    public static function getService($serviceId)
    {
        if (! isset(self::$_iocContainer)) {

            return null;
        }

        return self::$_iocContainer->get($serviceId);
    }
}