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
 * Injects Ajax pagination code into the gallery's HTML.
 */
class tubepress_app_feature_gallery_impl_listeners_html_AsyncGalleryInitJsListener
{
    /**
     * @var tubepress_lib_event_api_EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var tubepress_app_options_api_ContextInterface
     */
    private $_context;

    /**
     * @var tubepress_app_options_api_ReferenceInterface
     */
    private $_optionsReference;

    public function __construct(tubepress_app_options_api_ContextInterface       $context,
                                tubepress_lib_event_api_EventDispatcherInterface $eventDispatcher,
                                tubepress_app_options_api_ReferenceInterface     $optionsReference)
    {
        $this->_context          = $context;
        $this->_eventDispatcher  = $eventDispatcher;
        $this->_optionsReference = $optionsReference;
    }

    public function onGalleryHtml(tubepress_lib_event_api_EventInterface $event)
    {
        $galleryId = $this->_context->get(tubepress_app_html_api_Constants::OPTION_GALLERY_ID);
        $jsEvent   = $this->_eventDispatcher->newEventInstance(array(), array(
            'page' => $event->getArgument('page'),
            'pageNumber' => $event->getArgument('pageNumber')
        ));

        $this->_eventDispatcher->dispatch(tubepress_app_feature_gallery_api_Constants::EVENT_GALLERY_INIT_JS, $jsEvent);

        $args   = $jsEvent->getSubject();
        $this->_deepConvertBooleans($args);
        $asJson = json_encode($args);
        $html   = $event->getSubject();

        $toReturn = $html . <<<EOT
<script type="text/javascript">
   var tubePressDomInjector = tubePressDomInjector || [], tubePressGalleryRegistrar = tubePressGalleryRegistrar || [];
       tubePressDomInjector.push(['loadGalleryJs']);
       tubePressGalleryRegistrar.push(['register', '$galleryId', $asJson ]);
</script>
EOT;

        $event->setSubject($toReturn);
    }

    private function _deepConvertBooleans(array &$candidate)
    {
        foreach ($candidate as $key => $value) {

            if (is_array($value)) {

                $this->_deepConvertBooleans($value);
                $candidate[$key] = $value;
            }

            if (!$this->_optionsReference->optionExists($key)) {

                continue;
            }

            if (!$this->_optionsReference->isBoolean($key)) {

                continue;
            }

            $candidate[$key] = (bool) $value;
        }
    }
}