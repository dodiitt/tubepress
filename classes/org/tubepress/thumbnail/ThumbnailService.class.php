<?php
/**
 * Copyright 2006, 2007, 2008, 2009 Eric D. Hough (http://ehough.com)
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
 * Handles the parsing of the meta info below each video thumbnail
 *
 */
interface org_tubepress_thumbnail_ThumbnailService
{
    public function getHtml($template, org_tubepress_video_Video $vid, org_tubepress_player_Player $player);
    
    public function setOptionsManager(org_tubepress_options_manager_OptionsManager $tpom);
    
    public function setMessageService(org_tubepress_message_MessageService $messageService);
}