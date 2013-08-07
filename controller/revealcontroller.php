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
use OCA\Reveal\Http\DownloadResponse;


class RevealController extends Controller {

	private $itemMapper;

	/**
	 * @var \OC\Files\View $view
	 */
	private $view;

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
	 */
	public function __construct($api, $request) {
		parent::__construct($api, $request);
		$this->view = new View('/' . $this->api->getUserId() . '/files');
	}

	protected function getPresentations() {
		$presentations = array();
		$files = $this->view->searchByMime('text/reveal');
		foreach ($files as $file) {
			$entry = array('url' => $file['path'], 'name' => $file['name'], 'size' => $file['size'], 'mtime' => $file['mtime'], 'id' => $file['fileid']);
			$entry['preview'] = $this->extractFirstSlide($this->view->file_get_contents($file['path']));
			$entry['title'] = substr($file['name'], 0, strpos($file['name'], '.'));
			//cant show links in the preview
			$entry['preview'] = str_replace(array('<a ', '</a>'), array('<span ', '</span>'), $entry['preview']);
			$presentations[] = $entry;
		}

		return $presentations;
	}

	protected function extractFirstSlide($content) {
		$start = strpos($content, '<section');
		$end = strpos($content, '</section>') + 10;
		return substr($content, $start, $end - $start);
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
			'presentations' => $this->getPresentations()
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
		$path = $this->view->getPath($fileId);
		if ($path) {
			$content = $this->view->file_get_contents($path);
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
		$path = $this->view->getPath($fileId);
		if ($path) {
			$content = $this->view->file_get_contents($path);
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
		$path = $_GET['path'];
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
