<?php
/**
 * Created V/06/04/2012
 * Updated V/26/10/2012
 * Version 7
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

class Luigifab_Versioning_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'versioning';

		$this->_headerText = $this->__('Upgrades log');
		$this->_removeButton('add');

		$this->_addButton('back', array(
			'label'   => $this->helper('adminhtml')->__('Back'),
			'onclick' => "location.href = '".$this->getUrl('*/*/index')."';",
			'class'   => 'back'
		));

		if (is_file($this->helper('versioning')->getLastlogFile())) {
			$this->_addButton('lastlog', array(
				'label'   => $this->__('Upgrade log'),
				'onclick' => "location.href = '".$this->getUrl('*/*/lastlog')."';",
				'class'   => 'go'
			));
		}

		$this->_addButton('delete', array(
			'label'   => $this->helper('adminhtml')->__('Delete'),
			'onclick' => "return luigifabVersioningDelete('".$this->getUrl('*/*/deletehistory')."');",
			'class'   => 'delete'
		));
	}
}