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
 * @covers tubepress_core_template_ioc_TemplateExtension
 */
class tubepress_test_core_template_ioc_TemplateExtensionTest extends tubepress_test_core_ioc_AbstractIocContainerExtensionTest
{

    /**
     * @return tubepress_core_template_ioc_TemplateExtension
     */
    protected function buildSut()
    {
        return  new tubepress_core_template_ioc_TemplateExtension();
    }

    protected function prepareForLoad()
    {
        $this->expectRegistration(
            tubepress_core_template_api_TemplateFactoryInterface::_,
            'tubepress_core_template_impl_contemplate_TemplateFactory'
        )->withArgument(new tubepress_api_ioc_Reference(tubepress_api_log_LoggerInterface::_))
         ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_util_LangUtilsInterface::_))
         ->withArgument(new tubepress_api_ioc_Reference(tubepress_core_theme_api_ThemeLibraryInterface::_))
         ->withArgument(new tubepress_api_ioc_Reference('ehough_filesystem_FilesystemInterface'));
    }

    protected function getExpectedServiceContructionMap()
    {
        return array(

            tubepress_core_template_api_TemplateFactoryInterface::_ => 'tubepress_core_template_impl_contemplate_TemplateFactory'
        );
    }

    protected function getExpectedExternalServicesMap()
    {
        $logger = $this->mock(tubepress_api_log_LoggerInterface::_);
        $logger->shouldReceive('isEnabled')->atLeast(1)->andReturn(true);

        return array(
            tubepress_api_util_LangUtilsInterface::_ => tubepress_api_util_LangUtilsInterface::_,
            tubepress_api_log_LoggerInterface::_ => $logger,
            tubepress_core_theme_api_ThemeLibraryInterface::_ => tubepress_core_theme_api_ThemeLibraryInterface::_,
            'ehough_filesystem_FilesystemInterface' => 'ehough_filesystem_FilesystemInterface'
        );
    }
}