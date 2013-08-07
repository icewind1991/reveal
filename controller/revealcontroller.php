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

namespace OCA\Reveal\Controller;

use OC\Files\View;
use OCA\AppFramework\Controller\Controller;
use OCA\AppFramework\Http\NotFoundResponse;
use OCA\AppFramework\Http\TextResponse;
use OCA\Reveal\BusinessLayer\Presentations;
use OCA\Reveal\Http\DownloadResponse;


class RevealController extends Controller {

	private $itemMapper;

	/**
	 * @var \OC\Files\View $view
	 */
	private $view;

	/**
	 * @var \OCA\Reveal\BusinessLayer\Presentations $presentations
	 */
	private $presentations;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 */
	public function __construct($api, $request) {
		parent::__construct($api, $request);
		$this->view = new View('/' . $this->api->getUserId() . '/files');
		$this->presentations = new Presentations($this->view);
	}


	/**
	 * ATTENTION!!!
	 * The following comments turn off security checks
	 * Please look up their meaning in the documentation!
	 *
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function index() {
		$templateName = 'main';
		$params = array(
			'presentations' => $this->presentations->getPresentations()
		);
		return $this->render($templateName, $params);
	}

	/**
	 * ATTENTION!!!
	 * The following comments turn off security checks
	 * Please look up their meaning in the documentation!
	 *
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function show() {
		$fileId = $this->params('fileid');
		$path = $this->presentations->searchById($fileId);
		if ($path) {
			$content = $this->presentations->getContent($path);
		} else {
			return new NotFoundResponse();
		}

		$templateName = 'show';
		$params = array(
			'content' => $content
		);
		return $this->render($templateName, $params);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function get() {
		$fileId = $this->params('fileid');
		$path = $this->presentations->searchById($fileId);
		if ($path) {
			$content = $this->presentations->getContent($path);
		} else {
			return new NotFoundResponse();
		}

		return new TextResponse($content);
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 *
	 * @brief renders the index page
	 * @return an instance of a Response implementation
	 */
	public function image() {
		$path = $this->params('path');
		if (!$this->view->file_exists($path)) {
			return new NotFoundResponse();
		}
		$mime = $this->view->getMimeType($path);
		if (substr($mime, 0, 5) !== 'image') {
			return new NotFoundResponse();
		}

		return new DownloadResponse($this->view, $path, $mime);
	}
}
