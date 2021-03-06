<?php
/**
 * Copyright 2006 - 2018 TubePress LLC (http://tubepress.com)
 *
 * This file is part of TubePress (http://tubepress.com)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * Retrieves settings from a PHP file.
 */
class tubepress_internal_boot_BootSettings implements tubepress_api_boot_BootSettingsInterface
{
    private static $_TOP_LEVEL_KEY_SYSTEM = 'system';
    private static $_TOP_LEVEL_KEY_USER   = 'user';

    private static $_2ND_LEVEL_KEY_CLASSLOADER = 'classloader';
    private static $_2ND_LEVEL_KEY_CACHE       = 'cache';
    private static $_2ND_LEVEL_KEY_ADDONS      = 'add-ons';
    private static $_2ND_LEVEL_KEY_URLS        = 'urls';

    private static $_3RD_LEVEL_KEY_CLASSLOADER_ENABLED = 'enabled';
    private static $_3RD_LEVEL_KEY_CACHE_KILLERKEY     = 'killerKey';
    private static $_3RD_LEVEL_KEY_CACHE_ENABLED       = 'enabled';
    private static $_3RD_LEVEL_KEY_CACHE_DIR           = 'directory';
    private static $_3RD_LEVEL_KEY_ADDONS_BLACKLIST    = 'blacklist';
    private static $_3RD_LEVEL_KEY_SERIALIZATION_ENC   = 'serializationEncoding';
    private static $_3RD_LEVEL_KEY_URL_BASE            = 'base';
    private static $_3RD_LEVEL_KEY_URL_AJAX            = 'ajax';
    private static $_3RD_LEVEL_KEY_URL_USERCONTENT     = 'userContent';

    /**
     * @var tubepress_api_log_LoggerInterface
     */
    private $_logger;

    /**
     * @var bool
     */
    private $_hasInitialized = false;

    /**
     * @var bool
     */
    private $_shouldLog = false;

    /**
     * @var array
     */
    private $_addonBlacklistArray = array();

    /**
     * @var boolean
     */
    private $_isClassLoaderEnabled;

    /**
     * @var boolean
     */
    private $_isCacheEnabled;

    /**
     * @var string
     */
    private $_systemCacheKillerKey;

    /**
     * @var string
     */
    private $_cacheDirectory;

    /**
     * @var string
     */
    private $_cachedUserContentDir;

    /**
     * @var string
     */
    private $_serializationEncoding;

    /**
     * @var tubepress_api_url_UrlInterface|null
     */
    private $_urlBase;

    /**
     * @var tubepress_api_url_UrlInterface|null
     */
    private $_urlAjax;

    /**
     * @var tubepress_api_url_UrlInterface|null
     */
    private $_urlUserContent;

    /**
     * @var tubepress_api_url_UrlFactoryInterface
     */
    private $_urlFactory;

    public function __construct(tubepress_api_log_LoggerInterface     $logger,
                                tubepress_api_url_UrlFactoryInterface $urlFactory)
    {
        $this->_logger     = $logger;
        $this->_shouldLog  = $logger->isEnabled();
        $this->_urlFactory = $urlFactory;
    }

    /**
     * @return string
     *
     * @api
     * @since 4.0.0
     */
    public function getSerializationEncoding()
    {
        $this->_init();

        return $this->_serializationEncoding;
    }

    /**
     * @return bool True if the cache killer key has been set by the user.
     */
    public function shouldClearCache()
    {
        $this->_init();

        return isset($_GET[$this->_systemCacheKillerKey]) && $_GET[$this->_systemCacheKillerKey] === 'true';
    }

    /**
     * @return array An array of names of add-ons that have been blacklisted.
     */
    public function getAddonBlacklistArray()
    {
        $this->_init();

        return $this->_addonBlacklistArray;
    }

    /**
     * @return bool True if classloader registration is enabled.
     */
    public function isClassLoaderEnabled()
    {
        $this->_init();

        return $this->_isClassLoaderEnabled;
    }

    /**
     * @return bool True if the container cache is enabled. False otherwise.
     */
    public function isSystemCacheEnabled()
    {
        $this->_init();

        return $this->_isCacheEnabled;
    }

    /**
     * @return string An absolute path on the filesystem where TubePress can store
     *                the compiled service container.
     */
    public function getPathToSystemCacheDirectory()
    {
        $this->_init();

        return $this->_cacheDirectory;
    }

    public function getUserContentDirectory()
    {
        if (!isset($this->_cachedUserContentDir)) {

            if (defined('TUBEPRESS_CONTENT_DIRECTORY')) {

                $this->_cachedUserContentDir = rtrim(TUBEPRESS_CONTENT_DIRECTORY, DIRECTORY_SEPARATOR);

            } else {

                if ($this->_isWordPress()) {

                    if (!defined('WP_CONTENT_DIR')) {

                        define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
                    }

                    $this->_cachedUserContentDir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'tubepress-content';

                } else {

                    $this->_cachedUserContentDir = TUBEPRESS_ROOT . DIRECTORY_SEPARATOR . 'tubepress-content';
                }
            }
        }

        return $this->_cachedUserContentDir;
    }

    /**
     * @api
     * @since 4.0.9
     *
     * @return tubepress_api_url_UrlInterface|null
     */
    public function getUrlBase()
    {
        $this->_init();

        return $this->_urlBase;
    }

    /**
     * @api
     * @since 4.0.9
     *
     * @return tubepress_api_url_UrlInterface|null
     */
    public function getUrlUserContent()
    {
        $this->_init();

        return $this->_urlUserContent;
    }

    /**
     * @api
     * @since 4.0.9
     *
     * @return tubepress_api_url_UrlInterface|null
     */
    public function getUrlAjaxEndpoint()
    {
        $this->_init();

        return $this->_urlAjax;
    }

    private function _init()
    {
        if ($this->_hasInitialized) {

            return;
        }

        $this->_readConfig();

        $this->_hasInitialized = true;
    }

    private function _readConfig()
    {
        $userContentDirectory = $this->getUserContentDirectory();
        $userSettingsFilePath = $userContentDirectory . '/config/settings.php';
        $configArray          = array();

        /**
         * The user has their own settings.php.
         */
        if (is_readable($userSettingsFilePath)) {

            $configArray = $this->_readUserConfig($userSettingsFilePath);

        } else {

            if ($this->_shouldLog) {

                $this->_log(sprintf('No readable settings file at <code>%s</code>', $userSettingsFilePath));
            }
        }

        $this->_mergeConfig($configArray);
    }

    private function _readUserConfig($settingsFilePath)
    {
        if ($this->_shouldLog) {

            $this->_log(sprintf('Detected candidate settings.php at <code>%s</code>', $settingsFilePath));
        }

        try {

            /**
             * Turn on output buffering to capture any accidental output from the settings file.
             */
            ob_start();

            /** @noinspection PhpIncludeInspection */
            $configArray = include $settingsFilePath;

            ob_end_clean();

            if (!is_array($configArray)) {

                throw new RuntimeException('settings.php did not return an array of config values.');
            }

            if ($this->_shouldLog) {

                $this->_log(sprintf('Successfully read config values from <code>%s</code>', $settingsFilePath));
            }

            return $configArray;

        } catch (Exception $e) {

            if ($this->_shouldLog) {

                $this->_log(sprintf('Could not read settings.php from <code>%s</code>: <code>%s</code>',
                    $settingsFilePath, $e->getMessage()));
            }
        }

        return array();
    }

    private function _mergeConfig(array $config)
    {
        $this->_addonBlacklistArray   = $this->_getAddonBlacklistArray($config);
        $this->_isClassLoaderEnabled  = $this->_getClassLoaderEnablement($config);
        $this->_systemCacheKillerKey  = $this->_getCacheKillerKey($config);
        $this->_cacheDirectory        = rtrim($this->_getSystemCacheDirectory($config), DIRECTORY_SEPARATOR);
        $this->_isCacheEnabled        = $this->_getCacheEnablement($config);
        $this->_serializationEncoding = $this->_getSerializationEncoding($config);
        $this->_urlAjax               = $this->_getUrl($config, self::$_3RD_LEVEL_KEY_URL_AJAX);
        $this->_urlBase               = $this->_getUrl($config, self::$_3RD_LEVEL_KEY_URL_BASE);
        $this->_urlUserContent        = $this->_getUrl($config, self::$_3RD_LEVEL_KEY_URL_USERCONTENT);

        if ($this->_shouldLog) {

            $this->_log('Final settings from settings.php below:');
            $this->_log(sprintf('Add-on blacklist: <code>%s</code>',       htmlspecialchars(json_encode($this->_addonBlacklistArray))));
            $this->_log(sprintf('Class loader enabled? <code>%s</code>',   $this->_isClassLoaderEnabled ? 'yes' : 'no'));
            $this->_log(sprintf('Cache directory: <code>%s</code>',        $this->_cacheDirectory));
            $this->_log(sprintf('Cache enabled? <code>%s</code>',          $this->_isCacheEnabled ? 'yes' : 'no'));
            $this->_log(sprintf('Serialization encoding: <code>%s</code>', $this->_serializationEncoding));
            $this->_log(sprintf('Ajax URL: <code>%s</code>',               $this->_urlAjax));
            $this->_log(sprintf('Base URL: <code>%s</code>',               $this->_urlBase));
            $this->_log(sprintf('User content URL: <code>%s</code>',       $this->_urlUserContent));
        }
    }

    private function _getUrl(array $config, $key)
    {
        if (!$this->_isAllSet($config, self::$_TOP_LEVEL_KEY_USER, self::$_2ND_LEVEL_KEY_URLS, $key)) {

            return null;
        }

        $candidate = $config[self::$_TOP_LEVEL_KEY_USER][self::$_2ND_LEVEL_KEY_URLS][$key];

        try {

            $toReturn = $this->_urlFactory->fromString($candidate);

            $toReturn->freeze();

            return $toReturn;

        } catch (InvalidArgumentException $e) {

            if ($this->_shouldLog) {

                $this->_logger->error("Unable to parse $key URL from settings.php");
            }

            return null;
        }
    }

    private function _getSystemCacheDirectory(array $config)
    {
        if (!$this->_isAllSet($config, self::$_TOP_LEVEL_KEY_SYSTEM, self::$_2ND_LEVEL_KEY_CACHE,
            self::$_3RD_LEVEL_KEY_CACHE_DIR)) {

            return $this->_getFilesystemCacheDirectory();
        }

        $path = $config[self::$_TOP_LEVEL_KEY_SYSTEM][self::$_2ND_LEVEL_KEY_CACHE][self::$_3RD_LEVEL_KEY_CACHE_DIR];
        $path = $this->_addVersionToPath($path);

        if ($path === $this->_createDirectoryIfNecessary($path)) {

            return $path;
        }

        if ($this->_shouldLog) {

            $this->_log(sprintf(
                'Unable to use <code>%s</code>, so we will instead use the system temp directory', $path
            ));
        }

        /**
         * eh, we tried.
         */
        return $this->_getFilesystemCacheDirectory();
    }

    private function _getAddonBlacklistArray(array $config)
    {
        $default = array();

        if (!$this->_isAllSet($config, self::$_TOP_LEVEL_KEY_SYSTEM, self::$_2ND_LEVEL_KEY_ADDONS,
            self::$_3RD_LEVEL_KEY_ADDONS_BLACKLIST)) {

            return $default;
        }

        $blackList = $config[self::$_TOP_LEVEL_KEY_SYSTEM][self::$_2ND_LEVEL_KEY_ADDONS]
        [self::$_3RD_LEVEL_KEY_ADDONS_BLACKLIST];

        if (!is_array($blackList)) {

            return $default;
        }

        return array_values($blackList);
    }

    private function _getClassLoaderEnablement(array $config)
    {
        $default = true;

        if (!$this->_isAllSet($config, self::$_TOP_LEVEL_KEY_SYSTEM, self::$_2ND_LEVEL_KEY_CLASSLOADER,
            self::$_3RD_LEVEL_KEY_CLASSLOADER_ENABLED)) {

            return $default;
        }

        $enabled = $config[self::$_TOP_LEVEL_KEY_SYSTEM][self::$_2ND_LEVEL_KEY_CLASSLOADER]
        [self::$_3RD_LEVEL_KEY_CLASSLOADER_ENABLED];

        if (!is_bool($enabled)) {

            return $default;
        }

        return (boolean) $enabled;
    }

    private function _getCacheEnablement(array $config)
    {
        $default = true;

        if (!$this->_isAllSet($config, self::$_TOP_LEVEL_KEY_SYSTEM, self::$_2ND_LEVEL_KEY_CACHE,
            self::$_3RD_LEVEL_KEY_CACHE_ENABLED)) {

            return $default;
        }

        $enabled = $config[self::$_TOP_LEVEL_KEY_SYSTEM][self::$_2ND_LEVEL_KEY_CACHE]
        [self::$_3RD_LEVEL_KEY_CACHE_ENABLED];

        if (!is_bool($enabled)) {

            return $default;
        }

        return (boolean) $enabled;
    }

    private function _getCacheKillerKey(array $config)
    {
        $default = 'tubepress_clear_system_cache';

        if (!$this->_isAllSet($config, self::$_TOP_LEVEL_KEY_SYSTEM, self::$_2ND_LEVEL_KEY_CACHE,
            self::$_3RD_LEVEL_KEY_CACHE_KILLERKEY)) {

            return $default;
        }

        $key = $config[self::$_TOP_LEVEL_KEY_SYSTEM][self::$_2ND_LEVEL_KEY_CACHE]
        [self::$_3RD_LEVEL_KEY_CACHE_KILLERKEY];

        if (!is_string($key) || $key == '') {

            return $default;
        }

        return $key;
    }

    private function _getSerializationEncoding(array $config)
    {
        $default = 'base64';

        if (!$this->_isAllSet($config, self::$_TOP_LEVEL_KEY_SYSTEM, self::$_2ND_LEVEL_KEY_CACHE,
            self::$_3RD_LEVEL_KEY_SERIALIZATION_ENC)) {

            return $default;
        }

        $encoding = $config[self::$_TOP_LEVEL_KEY_SYSTEM][self::$_2ND_LEVEL_KEY_CACHE]
            [self::$_3RD_LEVEL_KEY_SERIALIZATION_ENC];

        if (!in_array($encoding, array('base64', 'none', 'urlencode'))) {

            return $default;
        }

        return $encoding;
    }

    private function _isAllSet(array $arr, $topLevel, $secondLevel, $thirdLevel)
    {
        if (!isset($arr[$topLevel])) {

            return false;
        }

        if (!isset($arr[$topLevel][$secondLevel])) {

            return false;
        }

        if (!isset($arr[$topLevel][$secondLevel][$thirdLevel])) {

            return false;
        }

        return true;
    }

    private function _getFilesystemCacheDirectory()
    {
        if (function_exists('sys_get_temp_dir')) {

            $tmp = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR;

        } else {

            $tmp = '/tmp/';
        }

        $baseDir = $tmp . 'tubepress-system-cache-' . md5(__DIR__) . DIRECTORY_SEPARATOR;
        $baseDir = $this->_addVersionToPath($baseDir);

        if ($baseDir === $this->_createDirectoryIfNecessary($baseDir)) {

            return $baseDir;
        }

        if (!$this->_isWordPress()) {

            /**
             * There's really nothing else we can do at this point.
             */
            return null;
        }

        /**
         * Let's try to use tubepress-content/system-cache as the cache directory.
         */
        $userContentDirectory = $this->getUserContentDirectory();
        $cacheDirectory       = $userContentDirectory . DIRECTORY_SEPARATOR . 'system-cache';
        $cacheDirectory       = $this->_addVersionToPath($cacheDirectory);

        if ($this->_shouldLog) {

            $this->_log(sprintf('Trying to use <code>%s</code> as the system cache directory instead.', $cacheDirectory));
        }

        if ($cacheDirectory === $this->_createDirectoryIfNecessary($cacheDirectory)) {

            return $cacheDirectory;
        }

        return null;
    }

    private function _isWordPress()
    {
        return defined('DB_NAME') && defined('ABSPATH');
    }
    
    private function _log($msg)
    {
        $this->_logger->debug(sprintf('(Boot Settings) %s', $msg));
    }

    private function _createDirectoryIfNecessary($path)
    {
        if ($this->_shouldLog) {

            $this->_log(sprintf('Seeing if we can use <code>%s</code> as our system cache directory', $path));
        }

        if (!is_dir($path)) {

            if ($this->_shouldLog) {

                $this->_log(sprintf('<code>%s</code> does not exist, so we will try to create it.', $path));
            }

            @mkdir($path, 0770, true);

            if (!is_dir($path)) {

                if ($this->_shouldLog) {

                    $this->_log(sprintf('Tried and failed to create <code>%s</code>.', $path));
                }

                return null;
            }
        }

        if (!is_writable($path)) {

            if ($this->_shouldLog) {

                $this->_log(sprintf('<code>%s</code> is a directory but we cannot write to it.', $path));
            }

            return null;
        }

        if ($this->_shouldLog) {

            $this->_log(sprintf('<code>%s</code> is a writable directory, so we should be able to use it.', $path));
        }

        return $path;
    }

    private function _addVersionToPath($path)
    {
        return rtrim($path, DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR .
            'tubepress-' .
            TUBEPRESS_VERSION;
    }
}
