<?php
/**
 * Created L/13/02/2012
 * Updated M/08/05/2012
 * Version 2
 *
 * Copyright 2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/versioning
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

class Luigifab_Versioning_Block_Adminhtml_Status extends Mage_Adminhtml_Block_Widget_View_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_status';
		$this->_blockGroup = 'versioning';

		$this->_headerText = $this->__('Current repository status');
		$this->_removeButton('edit');

		$this->_addButton('back', array(
			'label'   => $this->helper('adminhtml')->__('Back'),
			'onclick' => "location.href = '".$this->getUrl('versioning/repository')."';",
			'class'   => 'back'
		));
	}

	public function getViewHtml() {
		return $this->helper('versioning')->getStatusContent();
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}