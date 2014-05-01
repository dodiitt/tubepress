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
 * Applies the embedded service name to the template.
 */
class tubepress_addons_core_impl_listeners_template_ThumbGalleryEmbeddedImplName
{
    /**
     * @var tubepress_api_options_ContextInterface
     */
    private $_context;

    public function __construct(tubepress_api_options_ContextInterface $context)
    {
        $this->_context = $context;
    }

    public function onGalleryTemplate(tubepress_api_event_EventInterface $event)
    {
        $template = $event->getSubject();
        $page     = $event->getArgument('videoGalleryPage');

        $template->setVariable(tubepress_api_const_template_Variable::EMBEDDED_IMPL_NAME, $this->_getEmbeddedServiceName($page));
    }

    private function _getEmbeddedServiceName(tubepress_api_video_VideoGalleryPage $page)
    {
        $stored      = $this->_context->get(tubepress_api_const_options_names_Embedded::PLAYER_IMPL);
        $videoArray  = $page->getVideos();

        /**
         * @var $randomVideo tubepress_api_video_Video
         */
        $randomVideo = $videoArray[array_rand($videoArray)];
        $provider    = $randomVideo->getAttribute(tubepress_api_video_Video::ATTRIBUTE_PROVIDER_NAME);

        $longTailWithYouTube = $stored === 'longtail' && $provider === 'youtube';

        $embedPlusWithYouTube = $stored === 'embedplus' && $provider === 'youtube';

        if ($longTailWithYouTube || $embedPlusWithYouTube) {

            return $stored;
        }

        return $provider;
    }
}
