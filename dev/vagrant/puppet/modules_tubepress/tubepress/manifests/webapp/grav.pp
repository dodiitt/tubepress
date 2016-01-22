#
# Copyright 2006 - 2016 TubePress LLC (http://tubepress.com)
#
#  This file is part of TubePress (http://tubepress.com)
#
#  This Source Code Form is subject to the terms of the Mozilla Public
#  License, v. 2.0. If a copy of the MPL was not distributed with this
#  file, You can obtain one at http://mozilla.org/MPL/2.0/.
#

#
# Installs and configures Grav
#
class tubepress::webapp::grav {

  apache::vhost { 'grav.tubepress-test.com':
    docroot => '/var/www/grav',
    proxy_pass_match => [
      {
        'path' => '^/(.*\.php(/.*)?)$',
        'url'  => 'fcgi://127.0.0.1:9000/var/www/grav/$1'
      }
    ],
    directories => [
      {
        'path'         => '/var/www/grav',
        directoryindex => '/index.php index.php',
        allow_override => 'All',
      }
    ],
    aliases => [
      {
        'alias' => '/tubepress',
        'path'  => '/var/www/tubepress'
      }
    ],
    notify => Service['apache2'],
  }
}