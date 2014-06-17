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
 * Plays videos with jqmodal.
 */
class tubepress_core_player_impl_BasePlayerLocation implements tubepress_core_player_api_PlayerLocationInterface
{

    /**
     * @var string[]
     */
    private $_templatePaths;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var string
     */
    private $_displayName;

    /**
     * @var string
     */
    private $_relativeJsUrl;

    /**
     * @var bool
     */
    private $_producesHtml;

    /**
     * @var bool
     */
    private $_htmlOnPageLoad;

    public function __construct($name,
                                $displayName,
                                array $templatePaths,
                                $relativeJsurl,
                                $producesHtml,
                                $htmlOnPageLoad)
    {
        $this->_templatePaths  = $templatePaths;
        $this->_name           = $name;
        $this->_relativeJsUrl  = $relativeJsurl;
        $this->_displayName    = $displayName;
        $this->_producesHtml   = $producesHtml;
        $this->_htmlOnPageLoad = $htmlOnPageLoad;
    }

    public function onSelectPlayerLocation(tubepress_core_event_api_EventInterface $event)
    {
        $requestedPlayerLocation = $event->getArgument('requestedPlayerLocationName');

        if ($requestedPlayerLocation === $this->getName()) {

            $event->setArgument('playerLocation', $this);
            $event->stopPropagation();
        }
    }

    /**
     * @return string[] The paths for the template factory.
     *
     * @api
     * @since 4.0.0
     */
    public function getPathsForTemplateFactory()
    {
        return $this->_templatePaths;
    }

    /**
     * @return string The name of this playerLocation. Never empty or null. All alphanumerics and dashes.
     *
     * @api
     * @since 4.0.0
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param tubepress_core_environment_api_EnvironmentInterface $environment
     *
     * @return tubepress_core_url_api_UrlInterface Gets the URL to this player location's JS init script.
     *
     * @api
     * @since 4.0.0
     */
    public function getPlayerJsUrl(tubepress_core_environment_api_EnvironmentInterface $environment)
    {
        $sysUrl = $environment->getBaseUrl()->getClone();

        $sysUrl->addPath($this->_relativeJsUrl);

        return $sysUrl;
    }

    /**
     * @return boolean True if this player location produces HTML, false otherwise.
     *
     * @api
     * @since 4.0.0
     */
    public function producesHtml()
    {
        return $this->_producesHtml;
    }

    /**
     * @return string The human-readable name of this player location.
     *
     * @api
     * @since 4.0.0
     */
    public function getUntranslatedDisplayName()
    {
        return $this->_displayName;
    }

    /**
     * @return bool True if this player location should show HTML when the gallery is initially loaded, false otherwise.
     *
     * @api
     * @since 4.0.0
     */
    public function displaysHtmlOnInitialGalleryLoad()
    {
        return $this->_htmlOnPageLoad;
    }
}