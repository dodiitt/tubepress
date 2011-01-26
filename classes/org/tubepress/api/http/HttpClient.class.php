<?php
/**
 * Copyright 2006 - 2010 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org)
 * 
 * TubePress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * TubePress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with TubePress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Handles HTTP client functionality.
 */
interface org_tubepress_api_http_HttpClient
{
    const HTTP_METHOD_GET  = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';

    const HTTP_HEADER_CONTENT_LENGTH = 'Content-Length';
    const HTTP_HEADER_USER_AGENT     = 'User-Agent';

    /**
     * Get.
     *
     * @param string $url URI resource.
     *
     * @return string Resulting body as a string (could be null)
     */
    function get($url);

    /**
     * Post.
     *
     * @param string $url   URI resource.
     * @param unknown $body The HTTP body.
     *
     * @return string Resulting body as a string (could be null)
     */
    function post($url, $body);
}

