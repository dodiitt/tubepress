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
abstract class tubepress_test_platform_impl_boot_helper_uncached_contrib_AbstractManifestTest extends tubepress_test_TubePressUnitTest
{
    /**
     * @param $pathToManifest
     *
     * @return tubepress_platform_api_addon_AddonInterface
     */
    protected function getAddonFromManifest($pathToManifest)
    {
        $mockUrlFactory = $this->mock(tubepress_platform_api_url_UrlFactoryInterface::_);
        $mockUrlFactory->shouldReceive('fromString')->andReturnUsing(function ($incoming) {

            $factory = new tubepress_platform_impl_url_puzzle_UrlFactory($_SERVER);
            return $factory->fromString($incoming);
        });

        $logger       = new tubepress_platform_impl_log_BootLogger(false);
        $bootSettings = new tubepress_platform_impl_boot_BootSettings($logger);
        $urlFactory   = new tubepress_platform_impl_url_puzzle_UrlFactory();
        $langUtils    = new tubepress_platform_impl_util_LangUtils();
        $stringUtils  = new tubepress_platform_impl_util_StringUtils();
        $finderFactory = new ehough_finder_FinderFactory();

        $manifestFinder = new tubepress_platform_impl_boot_helper_uncached_contrib_ManifestFinder(

            dirname($pathToManifest), 'whatevs', 'manifest.json', $logger, $bootSettings, $finderFactory
        );
        $addonFactory = new tubepress_platform_impl_boot_helper_uncached_contrib_AddonFactory(
            $logger, $urlFactory, $langUtils, $stringUtils, $bootSettings
        );
        $addonManifests = $manifestFinder->find();

        $this->assertTrue(count($addonManifests) === 1, 'Expected 1 add-on but got ' . count($addonManifests));

        $addons = array();

        foreach ($addonManifests as $manifestPath => $contents) {

            $addons[] = $addonFactory->fromManifestData($manifestPath, $contents);
        }

        $this->assertTrue($addons[0] instanceof tubepress_platform_api_addon_AddonInterface);

        return $addons[0];
    }

    public function testCompilerPassesExist()
    {
        $addon = $this->getAddonFromManifest($this->getPathToManifest());

        $compilerPasses = $addon->getMapOfCompilerPassClassNamesToPriorities();

        $this->assertTrue(is_array($compilerPasses));

        foreach ($compilerPasses as $pass => $priority) {

            $this->assertTrue(class_exists($pass), "$pass is not a valid container compiler pass");
            $this->assertTrue(is_numeric($priority), "$pass must have a numeric priority");
        }
    }

    public function testIocContainerExtensionsExist()
    {
        $addon = $this->getAddonFromManifest($this->getPathToManifest());

        $extensions = $addon->getExtensionClassNames();

        $this->assertTrue(is_array($extensions));

        foreach ($extensions as $extension) {

            $this->assertTrue(class_exists($extension), "$extension is not a valid container extension");
        }
    }

    public function testClassMapIntegrity()
    {
        $map      = \Symfony\Component\ClassLoader\ClassMapGenerator::createMap(dirname($this->getPathToManifest()));
        $missing  = array();
        $manifest = $this->_decodeManifest();
        $toIgnore = $this->getClassNamesToIgnore();

        foreach ($map as $className => $path) {

            if (in_array($className, $toIgnore)) {

                continue;
            }

            if (!array_key_exists($className, $manifest['autoload']['classmap'])) {

                $missing[] = $className;
            }
        }

        if (!empty($missing)) {

            $missing = array_unique($missing);
            sort($missing);

            $message = "The following classes are missing from the manifest's classmap: \n\n" . implode("\n", $missing);
            $this->fail($message);
            return;
        }

        $extra = array_diff(array_keys($manifest['autoload']['classmap']), array_keys($map));

        if (!empty($extra)) {

            $message = "The following extra classes are in the manifest's classmap: \n\n" . implode("\n", $extra);
            $this->fail($message);
            return;
        }

        foreach ($manifest['autoload']['classmap'] as $className => $path) {

            if (!is_file(dirname($this->getPathToManifest()) . DIRECTORY_SEPARATOR . $path)) {

                $this->fail(dirname(realpath($this->getPathToManifest())) . DIRECTORY_SEPARATOR . $path . ' does not exist');
                return;
            }
        }

        $this->assertTrue(true);
    }

    private function _decodeManifest()
    {
        return json_decode(file_get_contents($this->getPathToManifest()), true);
    }

    protected abstract function getPathToManifest();

    protected function getClassNamesToIgnore()
    {
        //override point
        return array();
    }
}