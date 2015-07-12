<?php
/**
 * Copyright 2006 - 2015 TubePress LLC (http://tubepress.com)
 *
 * This file is part of TubePress (http://tubepress.com)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * @covers tubepress_theme_ioc_ThemeExtension
 */
class tubepress_test_theme_ioc_ThemeExtensionTest extends tubepress_test_platform_impl_ioc_AbstractContainerExtensionTest
{
    /**
     * @return tubepress_theme_ioc_ThemeExtension
     */
    protected function buildSut()
    {
        return  new tubepress_theme_ioc_ThemeExtension();
    }

    protected function prepareForLoad()
    {
        $this->_expectSingletonServices();
        $this->_expectListeners();
        $this->_expectOptions();
        $this->_expectOptionsUi();
    }

    private function _expectSingletonServices()
    {
        $this->expectRegistration(
            'tubepress_internal_boot_helper_uncached_Serializer',
            'tubepress_internal_boot_helper_uncached_Serializer'
        )->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_platform_api_boot_BootSettingsInterface::_));

        $parallelServices = array(
            array('',       '',       tubepress_app_api_options_Names::THEME),
            array('.admin', 'admin-', tubepress_app_api_options_Names::THEME_ADMIN),
        );

        foreach ($parallelServices as $serviceInfo) {

            $serviceSuffix  = $serviceInfo[0];
            $artifactPrefix = $serviceInfo[1];
            $optionName     = $serviceInfo[2];

            $this->expectRegistration(
                tubepress_platform_api_contrib_RegistryInterface::_ . '.' . tubepress_app_api_theme_ThemeInterface::_ . $serviceSuffix,
                'tubepress_internal_boot_helper_uncached_contrib_SerializedRegistry'
            )->withArgument(sprintf('%%%s%%', tubepress_internal_boot_PrimaryBootstrapper::CONTAINER_PARAM_BOOT_ARTIFACTS))
                ->withArgument($artifactPrefix . 'themes')
                ->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_platform_api_log_LoggerInterface::_))
                ->withArgument(new tubepress_platform_api_ioc_Reference('tubepress_internal_boot_helper_uncached_Serializer'));

            $this->expectRegistration(
                'tubepress_theme_impl_CurrentThemeService' . $serviceSuffix,
                'tubepress_theme_impl_CurrentThemeService'
            )->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_app_api_options_ContextInterface::_))
                ->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_platform_api_contrib_RegistryInterface::_ . '.' . tubepress_app_api_theme_ThemeInterface::_ . $serviceSuffix))
                ->withArgument('tubepress/' . $artifactPrefix . 'default')
                ->withArgument($optionName);
        }
    }

    private function _expectListeners()
    {
        $this->expectRegistration(
            'tubepress_theme_impl_listeners_LegacyThemeListener',
            'tubepress_theme_impl_listeners_LegacyThemeListener'
        )->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_platform_api_log_LoggerInterface::_))
            ->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_platform_api_contrib_RegistryInterface::_ . '.' . tubepress_app_api_theme_ThemeInterface::_))
            ->withTag(tubepress_lib_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_app_api_event_Events::OPTION_SET . '.' . tubepress_app_api_options_Names::THEME,
                'priority' => 100000,
                'method'   => 'onPreValidationSet'
            ));

        $this->expectRegistration(
            'tubepress_theme_impl_listeners_AcceptableValuesListener',
            'tubepress_theme_impl_listeners_AcceptableValuesListener'
        )->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_platform_api_contrib_RegistryInterface::_ . '.' . tubepress_app_api_theme_ThemeInterface::_))
            ->withTag(tubepress_lib_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_app_api_event_Events::OPTION_ACCEPTABLE_VALUES . '.' . tubepress_app_api_options_Names::THEME,
                'priority' => 100000,
                'method'   => 'onAcceptableValues'
            ));

        $this->expectRegistration(
            'tubepress_theme_impl_listeners_AcceptableValuesListener.admin',
            'tubepress_theme_impl_listeners_AcceptableValuesListener'
        )->withArgument(new tubepress_platform_api_ioc_Reference(tubepress_platform_api_contrib_RegistryInterface::_ . '.' . tubepress_app_api_theme_ThemeInterface::_ . '.admin'))
            ->withTag(tubepress_lib_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_app_api_event_Events::OPTION_ACCEPTABLE_VALUES . '.' . tubepress_app_api_options_Names::THEME_ADMIN,
                'priority' => 100000,
                'method'   => 'onAcceptableValues'
            ));
    }

    private function _expectOptions()
    {
        $this->expectRegistration(
            'tubepress_app_api_options_Reference__theme',
            'tubepress_app_api_options_Reference'
        )->withTag(tubepress_app_api_options_ReferenceInterface::_)
            ->withArgument(array(

                tubepress_app_api_options_Reference::PROPERTY_DEFAULT_VALUE => array(
                    tubepress_app_api_options_Names::THEME       => 'tubepress/default',
                    tubepress_app_api_options_Names::THEME_ADMIN => 'tubepress/admin-default',
                ),
                tubepress_app_api_options_Reference::PROPERTY_UNTRANSLATED_LABEL => array(
                    tubepress_app_api_options_Names::THEME => 'Theme',  //>(translatable)<
                ),
            ));
    }

    private function _expectOptionsUi()
    {
        $this->expectRegistration(
            'theme_field',
            'tubepress_app_api_options_ui_FieldInterface'
        )->withFactoryService(tubepress_app_api_options_ui_FieldBuilderInterface::_)
            ->withFactoryMethod('newInstance')
            ->withArgument(tubepress_app_api_options_Names::THEME)
            ->withArgument('theme');

        $fieldReferences = array(new tubepress_platform_api_ioc_Reference('theme_field'));

        $this->expectRegistration(
            'theme_category',
            'tubepress_options_ui_impl_BaseElement'
        )->withArgument(tubepress_app_api_options_ui_CategoryNames::THEME)
            ->withArgument('Theme'); //>(translatable)<

        $categoryReferences = array(new tubepress_platform_api_ioc_Reference('theme_category'));

        $fieldMap = array(
            tubepress_app_api_options_ui_CategoryNames::THEME => array(
                tubepress_app_api_options_Names::THEME
            ),
        );

        $this->expectRegistration(
            'tubepress_api_options_ui_BaseFieldProvider__theme',
            'tubepress_api_options_ui_BaseFieldProvider'
        )->withArgument('field-provider-theme')
            ->withArgument('Theme')
            ->withArgument(false)
            ->withArgument(false)
            ->withArgument($categoryReferences)
            ->withArgument($fieldReferences)
            ->withArgument($fieldMap);
    }

    protected function getExpectedParameterMap()
    {
        $theme = new tubepress_app_impl_theme_FilesystemTheme('the name', '1.2.3', 'the title',
            array(array('name' => 'eric hough')), array(array('url' => 'http://foo.bar/hi')));

        $adminTheme = new tubepress_app_impl_theme_FilesystemTheme('the admin name', '1.2.3', 'the admin title',
            array(array('name' => 'eric hough')), array(array('url' => 'http://foo.bar.admin/hi')));

        return array(
            tubepress_internal_boot_PrimaryBootstrapper::CONTAINER_PARAM_BOOT_ARTIFACTS => array(
                'themes'       => base64_encode(serialize(array($theme))),
                'admin-themes' => base64_encode(serialize(array($adminTheme)))
            )
        );
    }

    protected function getExpectedExternalServicesMap()
    {
        $mockBootSettings = $this->mock(tubepress_platform_api_boot_BootSettingsInterface::_);
        $mockBootSettings->shouldReceive('getSerializationEncoding')->twice()->andReturn('base64');

        $fieldBuilder = $this->mock(tubepress_app_api_options_ui_FieldBuilderInterface::_);
        $mockField    = $this->mock('tubepress_app_api_options_ui_FieldInterface');
        $fieldBuilder->shouldReceive('newInstance')->atLeast(1)->andReturn($mockField);

        return array(
            tubepress_platform_api_boot_BootSettingsInterface::_  => $mockBootSettings,
            tubepress_platform_api_log_LoggerInterface::_         => tubepress_platform_api_log_LoggerInterface::_,
            tubepress_app_api_options_ContextInterface::_         => tubepress_app_api_options_ContextInterface::_,
            tubepress_app_api_options_ui_FieldBuilderInterface::_ => $fieldBuilder,
        );
    }
}
