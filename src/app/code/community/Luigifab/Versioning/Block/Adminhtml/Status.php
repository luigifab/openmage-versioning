<?php
/**
 * Created L/13/02/2012
 * Updated V/15/05/2020
 *
 * Copyright 2011-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/versioning
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

class Luigifab_Versioning_Block_Adminhtml_Status extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();
		$system = $this->helper('versioning')->getSystem();

		$type = $system->getType();
		$from = $this->getRequest()->getParam('from');
		$to   = $this->getRequest()->getParam('to');

		if (!empty($from)) {
			$this->_controller = 'adminhtml_status';
			$this->_blockGroup = 'versioning';
			$this->_headerText = empty($branch = $system->getCurrentBranch()) ?
				$this->__('Differences between revisions %s and %s (<span id="scmtype">%s</span>)', $from, $to, $type) :
				$this->__('Differences between revisions %s and %s (<span id="scmtype">%s</span>, %s)', $from, $to, $type, $branch);
		}
		else {
			$this->_controller = 'adminhtml_status';
			$this->_blockGroup = 'versioning';
			$this->_headerText = empty($branch = $system->getCurrentBranch()) ?
				$this->__('Repository status (<span id="scmtype">%s</span>)', $type) :
				$this->__('Repository status (<span id="scmtype">%s</span>, %s)', $type, $branch);
		}

		$this->_removeButton('add');

		$this->_addButton('back', [
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		]);

		$this->_addButton('log', [
			'label'   => $this->__('Updates history'),
			'onclick' => "setLocation('".$this->getUrl('*/*/history')."');",
			'class'   => 'go'
		]);

		if (!empty($from)) {
			$this->_addButton('status', [
				'label'   => $this->__('Repository status'),
				'onclick' => "setLocation('".$this->getUrl('*/*/status')."');",
				'class'   => 'go'
			]);
		}
	}

	public function getGridHtml() {

		$system = $this->helper('versioning')->getSystem();
		$from = $this->getRequest()->getParam('from');
		$to   = $this->getRequest()->getParam('to');
		$dir  = $this->getRequest()->getParam('dir');
		$excl = $this->getRequest()->getParam('excl');

		if (!empty($dir))
			$dir = str_replace(['"','\'','|','\\'], '', $dir);

		if (!empty($from))
			return '<pre lang="mul">'.$system->getCurrentDiffStatus($from, $to, $dir).'</pre>'.
			       '<pre lang="mul">'.$system->getCurrentDiff($from, $to, $dir, $excl).'</pre>';
		else
			return '<pre lang="mul">'.$system->getCurrentStatus($dir).'</pre>'.
			       '<pre lang="mul">'.$system->getCurrentDiff(null, null, $dir, $excl).'</pre>';
	}

	public function getHeaderCssClass() {
		return 'icon-head '.parent::getHeaderCssClass().' '.$this->helper('versioning')->getSystem()->getType();
	}

	protected function _prepareLayout() {
		// nothing to do
	}
}