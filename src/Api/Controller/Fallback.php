<?php
/**
 * This is the default controller for the API app. It throws the 404 error on all actions.
 * 
 * @extends \Core\Controller\Generic
 */

namespace Maleficarum\Api\Controller;

class Fallback extends \Maleficarum\Api\Controller\Generic {
	/**
	 * @see \Core\Controller\Api\Generic._remap()
	 */
	public function _remap($method) {
		throw new \Maleficarum\Api\Exception\NotFoundException('404 - not found.');
	}
}
