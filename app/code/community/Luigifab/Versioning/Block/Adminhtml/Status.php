<?php
/**
 * Created L/13/02/2012
 * Updated S/21/02/2015
 * Version 14
 *
 * Copyright 2011-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Block_Adminhtml_Status extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_status';
		$this->_blockGroup = 'versioning';
		$this->_headerText = (!is_null($branch = Mage::registry('versioning')->getCurrentBranch())) ?
			$this->__('Repository status (<span id="scmtype">%s</span>, %s)', Mage::getStoreConfig('versioning/scm/type'), $branch) :
			$this->__('Repository status (<span id="scmtype">%s</span>)', Mage::getStoreConfig('versioning/scm/type'));

		$this->_removeButton('add');

		$this->_addButton('back', array(
			'label'   => $this->helper('adminhtml')->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		));

		$this->_addButton('history', array(
			'label'   => $this->__('Upgrades log'),
			'onclick' => "setLocation('".$this->getUrl('*/*/history')."');",
			'class'   => 'go'
		));
	}

	public function getGridHtml() {
		$model = Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type'));
		return '<pre>'.$model->getCurrentStatus().'</pre><pre>'.$model->getCurrentDiff().'</pre>';
	}

	public function getHeaderCssClass() {
		return 'icon-head '.parent::getHeaderCssClass().' '.Mage::getStoreConfig('versioning/scm/type');
	}

	protected function _prepareLayout() {
		// nothing to do
	}
}