<?php

/**
 * ownCloud - Reveal
 *
 * @author Robin Appelman
 * @copyright 2013 Robin Appelman <icewind@owncloud.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Reveal;

use \OCA\AppFramework\App;

use \OCA\Reveal\DependencyInjection\DIContainer;


/*************************
 * Define your routes here
 ************************/

/**
 * Normal Routes
 */
$this->create('reveal_index', '/')->action(
	function($params){
		App::main('RevealController', 'index', $params, new DIContainer());
	}
);

$this->create('reveal_show', '/{fileid}')->action(
	function($params){
		App::main('RevealController', 'show', $params, new DIContainer());
	}
);

$this->create('reveal_get', '/get/{fileid}')->get()->action(
	function($params){
		App::main('RevealController', 'get', $params, new DIContainer());
	}
);

$this->create('reveal_image', '/image/')->get()->action(
	function($params){
		App::main('RevealController', 'image', $params, new DIContainer());
	}
);
