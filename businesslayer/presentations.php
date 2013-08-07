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

namespace OCA\Reveal\BusinessLayer;

class Presentations {
	/**
	 * @var \OC\Files\View $view
	 */
	private $view;

	/**
	 * @param \OC\Files\View $view
	 */
	public function __construct($view) {
		$this->view = $view;
	}

	public function getPresentations() {
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

	public function extractFirstSlide($content) {
		$start = strpos($content, '<section');
		$end = strpos($content, '</section>') + 10;
		return substr($content, $start, $end - $start);
	}

	public function extractTitle($content) {
		$start = strpos($content, '<title>');
		$end = strpos($content, '</title>');
		if ($start !== false) {
			$start += 7;
			return substr($content, $start, $end - $start);
		}
	}

	public function getContent($path){
		return $this->view->file_get_contents($path);
	}

	public function searchById($fileId){
		return $this->view->getPath($fileId);
	}
}
