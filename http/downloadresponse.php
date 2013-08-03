<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
 * @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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


namespace OCA\Reveal\Http;


/**
 * Prompts the user to download the a textfile
 */
class DownloadResponse extends \OCA\AppFramework\Http\DownloadResponse {

	/**
	 * @var \OC\Files\View $view
	 */
	private $view;
	private $path;

	/**
	 * Creates a response that prompts the user to download a file which
	 * contains the passed string
	 *
	 * @param \OC\Files\View $view
	 * @param string $path the content that should be written into the file
	 * @param string $filename the name that the downloaded file should have
	 * @param string $contentType the mimetype that the downloaded file should have
	 */
	public function __construct($view, $path, $contentType) {
		parent::__construct(basename($path), $contentType);
		$this->view = $view;
		$this->path = $path;
		$this->cacheFor(3600);
	}


	/**
	 * Simply sets the headers and returns the file contents
	 *
	 * @return string the file contents
	 */
	public function render() {
		$mtime = $this->view->filemtime($this->path);
		$this->setLastModified(\DateTime::createFromFormat('U', $mtime));
//		$this->addHeader('Last-Modified', gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
		return $this->view->file_get_contents($this->path);
	}


}
