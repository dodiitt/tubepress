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
 * 
 * Uber simple/fast template for TubePress. Idea from here: http://seanhess.net/posts/simple_templating_system_in_php
 * Sure, maybe your templating system of choice looks prettier but I'll bet it's not faster :)
 */
?>
<div class="span-24 last">
	
	<div class="error">
		<strong>Access denied: <?php echo ${org_tubepress_uploads_admin_SecurityHandler::AUTH_ATTEMPT}? 'bad password' : 'password required'; ?></strong>
	</div>

	<form id="authenticate" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<fieldset>
			<p>
				<label for="<?php echo ${org_tubepress_uploads_admin_SecurityHandler::PASSWORD_PARAM_NAME}; ?>">Please enter the TubePress uploads admin page password</label>
				<br />
				<input type="password" id="<?php echo ${org_tubepress_uploads_admin_SecurityHandler::PASSWORD_PARAM_NAME}; ?>" class="title" name="<?php echo ${org_tubepress_uploads_admin_SecurityHandler::PASSWORD_PARAM_NAME}; ?>" /> 
			</p>
			<p>
				<input type="submit" value="Authenticate" />
			</p>
		</fieldset>
	</form>
</div>