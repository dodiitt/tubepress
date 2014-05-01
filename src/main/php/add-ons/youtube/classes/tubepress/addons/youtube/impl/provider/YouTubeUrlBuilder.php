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
 * Builds URLs to send out to YouTube for gdata
 *
 */
class tubepress_addons_youtube_impl_provider_YouTubeUrlBuilder implements tubepress_spi_provider_UrlBuilder
{
    private static $_URL_PARAM_FORMAT      = 'format';
    private static $_URL_PARAM_KEY         = 'key';
    private static $_URL_PARAM_MAX_RESULTS = 'max-results';
    private static $_URL_PARAM_ORDER       = 'orderby';
    private static $_URL_PARAM_SAFESEARCH  = 'safeSearch';
    private static $_URL_PARAM_START_INDEX = 'start-index';
    private static $_URL_PARAM_VERSION     = 'v';

    /**
     * @var tubepress_api_url_UrlFactoryInterface
     */
    private $_urlFactory;

    /**
     * @var tubepress_api_options_ContextInterface
     */
    private $_context;

    public function __construct(
        tubepress_api_options_ContextInterface $context,
        tubepress_api_url_UrlFactoryInterface $urlFactory)
    {
        $this->_urlFactory = $urlFactory;
        $this->_context    = $context;
    }

    /**
     * Builds a gdata request url for a list of videos
     *
     * @param int $currentPage The current page of the gallery.
     *
     * @return string The gdata request URL for this gallery
     */
    public final function buildGalleryUrl($currentPage)
    {
        switch ($this->_context->get(tubepress_api_const_options_names_Output::GALLERY_SOURCE)) {

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_USER:

                $url = 'users/' . $this->_context->get(tubepress_addons_youtube_api_const_options_names_GallerySource::YOUTUBE_USER_VALUE) . '/uploads';

                break;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_MOST_POPULAR:

                $url = 'standardfeeds/most_popular?time=' . $this->_context->get(tubepress_addons_youtube_api_const_options_names_GallerySource::YOUTUBE_MOST_POPULAR_VALUE);

                break;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_PLAYLIST:

                $url = 'playlists/' . $this->_context->get(tubepress_addons_youtube_api_const_options_names_GallerySource::YOUTUBE_PLAYLIST_VALUE);

                break;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_RELATED:

                $url = 'videos/' . $this->_context->get(tubepress_addons_youtube_api_const_options_names_GallerySource::YOUTUBE_RELATED_VALUE) . '/related';

                break;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_FAVORITES:

                $url = 'users/' . $this->_context->get(tubepress_addons_youtube_api_const_options_names_GallerySource::YOUTUBE_FAVORITES_VALUE) . '/favorites';

                break;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_SEARCH:

                $tags = $this->_context->get(tubepress_addons_youtube_api_const_options_names_GallerySource::YOUTUBE_TAG_VALUE);
                $tags = self::_replaceQuotes($tags);
                $tags = urlencode($tags);
                $url  = "videos?q=$tags";

                $filter = $this->_context->get(tubepress_api_const_options_names_Feed::SEARCH_ONLY_USER);

                if ($filter != '') {

                    $url .= "&author=$filter";
                }

                break;

            default:

                throw new Exception('Invalid source supplied to YouTube');
        }

        $requestUrl = $this->_urlFactory->fromString("http://gdata.youtube.com/feeds/api/$url");

        $this->_urlPostProcessingCommon($requestUrl);

        $this->_urlPostProcessingGallery($requestUrl, $currentPage);

        return $this->_finishUrl($requestUrl, tubepress_addons_youtube_api_const_YouTubeEventNames::URL_GALLERY);
    }

    /**
     * Build the URL for a single video.
     *
     * @param string $id The video ID.
     *
     * @throws InvalidArgumentException If we can't build a URL for the given ID.
     *
     * @return string The URL for the video.
     */
    public final function buildSingleVideoUrl($id)
    {
        $requestURL = $this->_urlFactory->fromString("http://gdata.youtube.com/feeds/api/videos/$id");

        $this->_urlPostProcessingCommon($requestURL);

        return $this->_finishUrl($requestURL, tubepress_addons_youtube_api_const_YouTubeEventNames::URL_SINGLE);
    }

    private function _finishUrl(tubepress_api_url_UrlInterface $url, $eventName)
    {
        $eventDispatcher = tubepress_impl_patterns_sl_ServiceLocator::getEventDispatcher();
        $event           = new tubepress_spi_event_EventBase($url);

        $eventDispatcher->dispatch($eventName, $event);

        /**
         * @var $url tubepress_api_url_UrlInterface
         */
        $finalUrl = $event->getSubject();

        return $finalUrl;
    }

    private static function _replaceQuotes($text)
    {
        return str_replace(array('&#8216', '&#8217', '&#8242;', '&#34', '&#8220;', '&#8221;', '&#8243;'), '"', $text);
    }

    private function _urlPostProcessingCommon(tubepress_api_url_UrlInterface $url)
    {
        $query                            = $url->getQuery();
        $query->set(self::$_URL_PARAM_VERSION, 2);
        $query->set(self::$_URL_PARAM_KEY, $this->_context->get(tubepress_addons_youtube_api_const_options_names_Feed::DEV_KEY));
    }

    private function _urlPostProcessingGallery(tubepress_api_url_UrlInterface $url, $currentPage)
    {
        $perPage = $this->_context->get(tubepress_api_const_options_names_Thumbs::RESULTS_PER_PAGE);

        /* start index of the videos */
        $start = ($currentPage * $perPage) - $perPage + 1;

        $query                              = $url->getQuery();
        $query->set(self::$_URL_PARAM_START_INDEX, $start);
        $query->set(self::$_URL_PARAM_MAX_RESULTS, $perPage);

        $this->_urlProcessingOrderBy($url);

        $query->set(self::$_URL_PARAM_SAFESEARCH, $this->_context->get(tubepress_addons_youtube_api_const_options_names_Feed::FILTER));

        if ($this->_context->get(tubepress_addons_youtube_api_const_options_names_Feed::EMBEDDABLE_ONLY)) {

            $query->set(self::$_URL_PARAM_FORMAT, '5');
        }
    }

    private function _urlProcessingOrderBy(tubepress_api_url_UrlInterface $url)
    {
        /*
         * In a request for a video feed, the following values are valid for this parameter:
         *
         * relevance – Entries are ordered by their relevance to a search query. This is the default setting for video search results feeds.
         * published – Entries are returned in reverse chronological order. This is the default value for video feeds other than search results feeds.
         * viewCount – Entries are ordered from most views to least views.
         * rating – Entries are ordered from highest rating to lowest rating.
         *
         * In a request for a playlist feed, the following values are valid for this parameter:
         *
         * position – Entries are ordered by their position in the playlist. This is the default setting.
         * commentCount – Entries are ordered by number of comments from most comments to least comments.
         * duration – Entries are ordered by length of each playlist video from longest video to shortest video.
         * published – Entries are returned in reverse chronological order.
         * reversedPosition – Entries are ordered in reverse of their position in the playlist.
         * title – Entries are ordered alphabetically by title.
         * viewCount – Entries are ordered from most views to least views.
         */

        $requestedSortOrder   = $this->_context->get(tubepress_api_const_options_names_Feed::ORDER_BY);
        $currentGallerySource = $this->_context->get(tubepress_api_const_options_names_Output::GALLERY_SOURCE);
        $query                = $url->getQuery();

        if ($requestedSortOrder === tubepress_api_const_options_values_OrderByValue::DEFAULTT) {

            $query->set(self::$_URL_PARAM_ORDER, $this->_calculateDefaultSearchOrder($currentGallerySource));
            return;
        }

        if ($requestedSortOrder === tubepress_api_const_options_values_OrderByValue::NEWEST) {

            $query->set(self::$_URL_PARAM_ORDER, 'published');
            return;
        }

        if ($requestedSortOrder == tubepress_api_const_options_values_OrderByValue::VIEW_COUNT) {

            $query->set(self::$_URL_PARAM_ORDER, $requestedSortOrder);

            return;
        }

        if ($currentGallerySource == tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_PLAYLIST) {

            if (in_array($requestedSortOrder, array(

                tubepress_api_const_options_values_OrderByValue::POSITION,
                tubepress_api_const_options_values_OrderByValue::COMMENT_COUNT,
                tubepress_api_const_options_values_OrderByValue::DURATION,
                tubepress_api_const_options_values_OrderByValue::REV_POSITION,
                tubepress_api_const_options_values_OrderByValue::TITLE,

            ))) {

                $query->set(self::$_URL_PARAM_ORDER, $requestedSortOrder);
                return;
            }

        } else {

            if (in_array($requestedSortOrder, array(tubepress_api_const_options_values_OrderByValue::RELEVANCE, tubepress_api_const_options_values_OrderByValue::RATING))) {

                $query->set(self::$_URL_PARAM_ORDER, $requestedSortOrder);
            }
        }
    }

    private function _calculateDefaultSearchOrder($currentGallerySource)
    {
        switch ($currentGallerySource) {

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_MOST_POPULAR:

                return tubepress_api_const_options_values_OrderByValue::VIEW_COUNT;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_PLAYLIST:

                return tubepress_api_const_options_values_OrderByValue::POSITION;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_SEARCH:
            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_RELATED:

                return tubepress_api_const_options_values_OrderByValue::RELEVANCE;

            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_USER:
            case tubepress_addons_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_FAVORITES:

                return 'published';
        }
    }
}