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
 * General purpose message abstraction for TubePress
 *
 * @api
 * @since 4.0.0
 */
interface tubepress_core_translation_api_TranslatorInterface
{
    /**
     * @ignore
     */
    const _ = 'tubepress_core_translation_api_TranslatorInterface';

    /**
     * Get the message corresponding to the given key.
     *
     * @param string $messageKey The message key.
     *
     * @return string The corresponding message.
     *
     * @api
     * @since 4.0.0
     */
    function _($messageKey);
}