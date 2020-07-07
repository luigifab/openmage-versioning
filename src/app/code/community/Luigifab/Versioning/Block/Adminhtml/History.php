<?php
/**
 * Created V/06/04/2012
 * Updated S/14/09/2019
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

class Luigifab_Versioning_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();
		$system = $this->helper('versioning')->getSystem();

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'versioning';
		$this->_headerText = empty($branch = $system->getCurrentBranch()) ?
			$this->__('Updates history (<span id="scmtype">%s</span>)', $system->getType()) :
			$this->__('Updates history (<span id="scmtype">%s</span>, %s)', $system->getType(), $branch);

		$this->_removeButton('add');

		$this->_addButton('back', [
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		]);

		$this->_addButton('status', [
			'label'   => $this->__('Repository status'),
			'onclick' => "setLocation('".$this->getUrl('*/*/status')."');",
			'class'   => 'go'
		]);
	}

	public function getGridHtml() {
		$file = $this->helper('versioning')->getLastLog();
		return '<pre lang="mul">'.(is_file($file) ? file_get_contents($file) : '').'</pre> '.$this->getChildHtml('grid');
	}

	public function getHeaderCssClass() {
		return 'icon-head '.parent::getHeaderCssClass().' '.$this->helper('versioning')->getSystem()->getType();
	}
}