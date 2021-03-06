<?php
/*
 * Copyright 2006 - 2018 TubePress LLC (http://tubepress.com)
 *
 * This file is part of TubePress (http://tubepress.com)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * @covers tubepress_vimeo3_ioc_VimeoExtension
 */
class tubepress_test_vimeo3_ioc_VimeoExtensionTest extends tubepress_api_test_ioc_AbstractContainerExtensionTest
{
    /**
     * @return tubepress_spi_ioc_ContainerExtensionInterface
     */
    protected function buildSut()
    {
        return new tubepress_vimeo3_ioc_VimeoExtension();
    }

    protected function prepareForLoad()
    {
        $this->_registerEmbedded();
        $this->_registerListeners();
        $this->_registerMediaProvider();
        $this->_registerOauthProvider();
        $this->_registerOptions();
        $this->_registerOptionsUi();
        $this->_registerPlayer();
    }

    private function _registerEmbedded()
    {
        $this->expectRegistration(
            'tubepress_vimeo3_impl_embedded_VimeoEmbeddedProvider',
            'tubepress_vimeo3_impl_embedded_VimeoEmbeddedProvider'
        )->withArgument(new tubepress_api_ioc_Reference(tubepress_api_options_ContextInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_util_LangUtilsInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_url_UrlFactoryInterface::_))
            ->withTag('tubepress_spi_embedded_EmbeddedProviderInterface')
            ->withTag('tubepress_spi_template_PathProviderInterface');
    }

    private function _registerListeners()
    {
        $this->expectRegistration(
            'tubepress_vimeo3_impl_listeners_media_HttpItemListener',
            'tubepress_vimeo3_impl_listeners_media_HttpItemListener'
        )->withArgument(new tubepress_api_ioc_Reference(tubepress_api_media_AttributeFormatterInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_options_ContextInterface::_))
            ->withTag(tubepress_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_api_event_Events::MEDIA_ITEM_HTTP_NEW . '.vimeo_v3',
                'method'   => 'onHttpItem',
                'priority' => 100000,
            ));

        $this->expectRegistration(
            'tubepress_api_options_listeners_TrimmingListener.' . tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR,
            'tubepress_api_options_listeners_TrimmingListener'
        )->withArgument('#')
            ->withTag(tubepress_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_api_event_Events::OPTION_SET . '.' . tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR,
                'method'   => 'onOption',
                'priority' => 100000,
            ));

        $validators = array(
            tubepress_api_options_listeners_RegexValidatingListener::TYPE_STRING_HEXCOLOR => array(
                tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR,
            ),
            tubepress_api_options_listeners_RegexValidatingListener::TYPE_ONE_OR_MORE_WORDCHARS => array(
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_ALBUM_VALUE,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_APPEARS_IN_VALUE,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_CHANNEL_VALUE,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_GROUP_VALUE,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_LIKES_VALUE,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_UPLOADEDBY_VALUE,
            ),
        );

        foreach ($validators as $type => $optionNames) {
            foreach ($optionNames as $optionName) {

                $this->expectRegistration(
                    "regex_validation.$optionName",
                    'tubepress_api_options_listeners_RegexValidatingListener'
                )->withArgument($type)
                    ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_options_ReferenceInterface::_))
                    ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_translation_TranslatorInterface::_));
            }
        }

        $this->expectRegistration(
            'tubepress_vimeo3_impl_listeners_options_VimeoOptionsListener',
            'tubepress_vimeo3_impl_listeners_options_VimeoOptionsListener'
        )->withArgument(new tubepress_api_ioc_Reference(tubepress_api_url_UrlFactoryInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_util_StringUtilsInterface::_))
            ->withTag(tubepress_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_api_event_Events::OPTION_SET . '.' . tubepress_vimeo3_api_Constants::OPTION_VIMEO_ALBUM_VALUE,
                'method'   => 'onAlbumValue',
                'priority' => 100000, ))
            ->withTag(tubepress_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_api_event_Events::OPTION_SET . '.' . tubepress_vimeo3_api_Constants::OPTION_VIMEO_GROUP_VALUE,
                'method'   => 'onGroupValue',
                'priority' => 100000, ))
            ->withTag(tubepress_api_ioc_ServiceTags::EVENT_LISTENER, array(
                'event'    => tubepress_api_event_Events::OPTION_SET . '.' . tubepress_vimeo3_api_Constants::OPTION_VIMEO_CHANNEL_VALUE,
                'method'   => 'onChannelValue',
                'priority' => 100000, ));
    }

    private function _registerMediaProvider()
    {
        $this->expectRegistration(
            'tubepress_vimeo3_impl_media_FeedHandler',
            'tubepress_vimeo3_impl_media_FeedHandler'
        )->withArgument(new tubepress_api_ioc_Reference(tubepress_api_log_LoggerInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_url_UrlFactoryInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_options_ContextInterface::_));

        $this->expectRegistration(
            'tubepress_vimeo3_impl_media_MediaProvider',
            'tubepress_vimeo3_impl_media_MediaProvider'
        )->withArgument(new tubepress_api_ioc_Reference(tubepress_api_media_HttpCollectorInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference('tubepress_vimeo3_impl_media_FeedHandler'))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_environment_EnvironmentInterface::_))
            ->withTag(tubepress_spi_media_MediaProviderInterface::__);
    }

    private function _registerOauthProvider()
    {
        $this->expectRegistration(
            'tubepress_vimeo3_impl_oauth_VimeoOauth2Provider',
            'tubepress_vimeo3_impl_oauth_VimeoOauth2Provider'
        )->withArgument(new tubepress_api_ioc_Reference(tubepress_api_url_UrlFactoryInterface::_))
            ->withArgument(new tubepress_api_ioc_Reference(tubepress_api_util_StringUtilsInterface::_))
            ->withTag(tubepress_spi_http_oauth2_Oauth2ProviderInterface::_);
    }

    private function _registerOptionsUi()
    {
        $fieldIndex = 0;

        $gallerySourceMap = array(

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_ALBUM,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_ALBUM_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_CHANNEL,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_CHANNEL_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_SEARCH,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_SEARCH_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_UPLOADEDBY,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_UPLOADEDBY_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_APPEARS_IN,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_APPEARS_IN_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_LIKES,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_LIKES_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_GROUP,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_GROUP_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_CATEGORY,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_CATEGORY_VALUE, ),

            array(tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_TAG,
                tubepress_vimeo3_api_Constants::OPTION_VIMEO_TAG_VALUE, ),
        );

        foreach ($gallerySourceMap as $gallerySourceFieldArray) {

            $this->expectRegistration(

                'vimeo_options_subfield_' . $fieldIndex,
                'tubepress_api_options_ui_FieldInterface'
            )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
                ->withFactoryMethod('newInstance')
                ->withArgument($gallerySourceFieldArray[1])
                ->withArgument('multiSourceText');

            $this->expectRegistration(

                'vimeo_options_field_' . $fieldIndex,
                'tubepress_api_options_ui_FieldInterface'
            )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
                ->withFactoryMethod('newInstance')
                ->withArgument($gallerySourceFieldArray[0])
                ->withArgument('gallerySourceRadio')
                ->withArgument(array(
                    'additionalField' => new tubepress_api_ioc_Reference('vimeo_options_subfield_' . $fieldIndex++),
                ));
        }

        $this->expectRegistration(

            'vimeo_options_field_' . $fieldIndex++,
            'tubepress_api_options_ui_FieldInterface'
        )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
            ->withFactoryMethod('newInstance')
            ->withArgument(tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR)
            ->withArgument('spectrum');

        $this->expectRegistration(

            'vimeo_options_field_' . $fieldIndex++,
            'tubepress_api_options_ui_FieldInterface'
        )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
            ->withFactoryMethod('newInstance')
            ->withArgument('does-not-matter')
            ->withArgument('oauth2ClientInstructions')
            ->withArgument(array(
                'provider' => new tubepress_api_ioc_Reference('tubepress_vimeo3_impl_oauth_VimeoOauth2Provider'),
            ));

        $this->expectRegistration(

            'vimeo_options_field_' . $fieldIndex++,
            'tubepress_api_options_ui_FieldInterface'
        )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
            ->withFactoryMethod('newInstance')
            ->withArgument('does-not-matter')
            ->withArgument('oauth2ClientId')
            ->withArgument(array(
                'provider' => new tubepress_api_ioc_Reference('tubepress_vimeo3_impl_oauth_VimeoOauth2Provider'),
            ));

        $this->expectRegistration(

            'vimeo_options_field_' . $fieldIndex++,
            'tubepress_api_options_ui_FieldInterface'
        )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
            ->withFactoryMethod('newInstance')
            ->withArgument('does-not-matter')
            ->withArgument('oauth2ClientSecret')
            ->withArgument(array(
                'provider' => new tubepress_api_ioc_Reference('tubepress_vimeo3_impl_oauth_VimeoOauth2Provider'),
            ));

        $this->expectRegistration(

            'vimeo_options_field_' . $fieldIndex++,
            'tubepress_api_options_ui_FieldInterface'
        )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
            ->withFactoryMethod('newInstance')
            ->withArgument('does-not-matter')
            ->withArgument('oauth2TokenManagement')
            ->withArgument(array(
                'provider' => new tubepress_api_ioc_Reference('tubepress_vimeo3_impl_oauth_VimeoOauth2Provider'),
            ));

        $this->expectRegistration(

            'vimeo_options_field_' . $fieldIndex++,
            'tubepress_api_options_ui_FieldInterface'
        )->withFactoryService(tubepress_api_options_ui_FieldBuilderInterface::_)
            ->withFactoryMethod('newInstance')
            ->withArgument('does-not-matter')
            ->withArgument('oauth2TokenSelection')
            ->withArgument(array(
                'provider' => new tubepress_api_ioc_Reference('tubepress_vimeo3_impl_oauth_VimeoOauth2Provider'),
            ));

        $fieldReferences = array();
        for ($x = 0; $x < $fieldIndex; ++$x) {
            $fieldReferences[] = new tubepress_api_ioc_Reference('vimeo_options_field_' . $x);
        }

        $this->expectRegistration(

            'tubepress_vimeo3_impl_options_ui_FieldProvider',
            'tubepress_vimeo3_impl_options_ui_FieldProvider'
        )->withArgument($fieldReferences)
            ->withArgument(array(

                tubepress_api_options_ui_CategoryNames::GALLERY_SOURCE => array(

                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_ALBUM,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_CHANNEL,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_SEARCH,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_UPLOADEDBY,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_APPEARS_IN,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_LIKES,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_GROUP,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_CATEGORY,
                    tubepress_vimeo3_api_Constants::GALLERYSOURCE_VIMEO_TAG,
                ),

                tubepress_api_options_ui_CategoryNames::EMBEDDED => array(

                    tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR,
                ),

                tubepress_api_options_ui_CategoryNames::FEED => array(

                    'clientInstructions_vimeoV3',
                    'clientId_vimeoV3',
                    'clientSecret_vimeoV3',
                    'tokenManagement_vimeoV3',
                    'tokenSelection_vimeoV3',
                ),
            ))
            ->withTag('tubepress_spi_options_ui_FieldProviderInterface');
    }

    private function _registerOptions()
    {
        $this->expectRegistration(
            'tubepress_api_options_Reference__vimeo',
            'tubepress_api_options_Reference'
        )->withTag(tubepress_api_options_ReferenceInterface::_)
            ->withArgument(array(

                tubepress_api_options_Reference::PROPERTY_DEFAULT_VALUE => array(
                    tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR           => '999999',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_ALBUM_VALUE      => '140484',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_APPEARS_IN_VALUE => 'royksopp',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_CHANNEL_VALUE    => 'splitscreenstuff',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_GROUP_VALUE      => 'hdxs',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_LIKES_VALUE      => 'coiffier',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_SEARCH_VALUE     => 'glacier national park',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_UPLOADEDBY_VALUE => 'AvantGardeDiaries',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_CATEGORY_VALUE   => 'documentary',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_TAG_VALUE        => 'weddings',
                    tubepress_vimeo3_api_Constants::OPTION_LIKES                  => false,
                ),

                tubepress_api_options_Reference::PROPERTY_UNTRANSLATED_LABEL => array(
                    tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR => 'Main color',

                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_ALBUM_VALUE      => 'Videos from this Vimeo album',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_APPEARS_IN_VALUE => 'Videos this Vimeo user appears in',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_CHANNEL_VALUE    => 'Videos in this Vimeo channel',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_GROUP_VALUE      => 'Videos from this Vimeo group',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_LIKES_VALUE      => 'Videos this Vimeo user likes',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_SEARCH_VALUE     => 'Vimeo search for',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_UPLOADEDBY_VALUE => 'Videos uploaded by this Vimeo user',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_CATEGORY_VALUE   => 'Videos in this Vimeo category',
                    tubepress_vimeo3_api_Constants::OPTION_VIMEO_TAG_VALUE        => 'Videos tagged with',

                    tubepress_vimeo3_api_Constants::OPTION_LIKES => 'Number of likes',
                ),

                tubepress_api_options_Reference::PROPERTY_UNTRANSLATED_DESCRIPTION => array(

                    tubepress_vimeo3_api_Constants::OPTION_PLAYER_COLOR => sprintf('Default is %s.', '999999'),
                ),
            ));
    }

    private function _registerPlayer()
    {
        $this->expectRegistration(
            'tubepress_vimeo3_impl_player_VimeoPlayerLocation',
            'tubepress_vimeo3_impl_player_VimeoPlayerLocation'
        )->withTag('tubepress_spi_player_PlayerLocationInterface');
    }

    protected function getExpectedExternalServicesMap()
    {
        $mockField        = $this->mock('tubepress_api_options_ui_FieldInterface');
        $mockfieldBuilder = $this->mock(tubepress_api_options_ui_FieldBuilderInterface::_);
        $mockfieldBuilder->shouldReceive('newInstance')->atLeast(1)->andReturn($mockField);

        $mockBaseUrl = $this->mock('tubepress_api_url_UrlInterface');
        $environment = $this->mock(tubepress_api_environment_EnvironmentInterface::_);
        $environment->shouldReceive('getBaseUrl')->once()->andReturn($mockBaseUrl);
        $mockBaseUrl->shouldReceive('getClone')->once()->andReturn($mockBaseUrl);
        $mockBaseUrl->shouldReceive('addPath')->once()->with('src/add-ons/provider-vimeo-v3/web/images/icons/vimeo-icon-34w_x_34h.png')->andReturn($mockBaseUrl);
        $mockBaseUrl->shouldReceive('toString')->once()->andReturn('icon-url');

        return array(

            tubepress_api_options_ContextInterface::_          => tubepress_api_options_ContextInterface::_,
            tubepress_api_url_UrlFactoryInterface::_           => tubepress_api_url_UrlFactoryInterface::_,
            tubepress_api_template_TemplatingInterface::_      => tubepress_api_template_TemplatingInterface::_,
            tubepress_api_util_LangUtilsInterface::_           => tubepress_api_util_LangUtilsInterface::_,
            tubepress_api_util_TimeUtilsInterface::_           => tubepress_api_util_TimeUtilsInterface::_,
            tubepress_api_log_LoggerInterface::_               => tubepress_api_log_LoggerInterface::_,
            tubepress_api_event_EventDispatcherInterface::_    => tubepress_api_event_EventDispatcherInterface::_,
            tubepress_api_options_ui_FieldBuilderInterface::_  => $mockfieldBuilder,
            tubepress_api_translation_TranslatorInterface::_   => tubepress_api_translation_TranslatorInterface::_,
            tubepress_api_options_ReferenceInterface::_        => tubepress_api_options_ReferenceInterface::_,
            tubepress_api_media_HttpCollectorInterface::_      => tubepress_api_media_HttpCollectorInterface::_,
            tubepress_api_media_AttributeFormatterInterface::_ => tubepress_api_media_AttributeFormatterInterface::_,
            tubepress_api_environment_EnvironmentInterface::_  => $environment,
            tubepress_api_util_StringUtilsInterface::_         => tubepress_api_util_StringUtilsInterface::_,
        );
    }
}
