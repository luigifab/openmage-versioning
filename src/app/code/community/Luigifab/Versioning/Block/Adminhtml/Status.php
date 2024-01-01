<?php
/**
 * Created L/13/02/2012
 * Updated D/17/12/2023
 *
 * Copyright 2011-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-versioning
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

		$type = '<span id="scmtype">'.$system->getType().'</span>';
		$from = $this->getRequest()->getParam('from');
		$to   = $this->getRequest()->getParam('to');

		if (empty($from)) {
			$this->_controller = 'adminhtml_status';
			$this->_blockGroup = 'versioning';
			$this->_headerText = empty($branch = $system->getCurrentBranch()) ?
				$this->__('Repository status (%s)', $type) :
				$this->__('Repository status (%s, %s)', $type, $branch);
		}
		else {
			$this->_controller = 'adminhtml_status';
			$this->_blockGroup = 'versioning';
			$this->_headerText = empty($branch = $system->getCurrentBranch()) ?
				$this->__('Differences between revisions %s and %s (%s)', $from, $to, $type) :
				$this->__('Differences between revisions %s and %s (%s, %s)', $from, $to, $type, $branch);
		}

		$this->_removeButton('add');

		$this->_addButton('back', [
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back',
		]);

		$this->_addButton('log', [
			'label'   => $this->__('Updates history'),
			'onclick' => "setLocation('".$this->getUrl('*/*/history')."');",
			'class'   => 'go',
		]);

		if (!empty($from)) {
			$this->_addButton('status', [
				'label'   => $this->__('Repository status'),
				'onclick' => "setLocation('".$this->getUrl('*/*/status')."');",
				'class'   => 'go',
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

		if (empty($from))
			$html = '<pre lang="mul">'.$system->getCurrentStatus($dir, $excl).'</pre>'.
			        '<pre lang="mul">'.$system->getCurrentDiff(null, null, $dir, $excl, true).'</pre>'.
			        '<pre lang="mul">'.$system->getCurrentDiff(null, null, $dir, $excl).'</pre>';
		else
			$html = '<pre lang="mul">'.$system->getCurrentDiffStatus($from, $to, $dir, $excl).'</pre>'.
			        '<pre lang="mul">'.$system->getCurrentDiff($from, $to, $dir, $excl).'</pre>';

		$dir = $system->getRootDir();

		// @see https://github.com/luigifab/webext-openfileeditor
		return preg_replace_callback('#(=== )(.*/.*\.\w+)</#', static function ($data) use ($dir) {
			return $data[1].'<span class="ofe openfileeditor" data-file="'.$dir.'/'.$data[2].'">'.$data[2].'</span></';
		}, $html);
	}

	public function getHeaderCssClass() {
		return 'icon-head '.parent::getHeaderCssClass().' '.$this->helper('versioning')->getSystem()->getType();
	}

	protected function _prepareLayout() {
		// nothing to do
	}
}