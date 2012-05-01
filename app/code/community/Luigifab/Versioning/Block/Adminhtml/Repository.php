<?php
/**
 * Created S/03/12/2011
 * Updated V/27/04/2012
 * Version 9
 *
 * Copyright 2011-2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Block_Adminhtml_Repository extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_repository';
		$this->_blockGroup = 'versioning';

		if (Mage::getStoreConfig('versioning/scm/type') === 'git')
			$this->_headerText = $this->__('Commit history (%s/%s)', Mage::getStoreConfig('versioning/scm/type'), Mage::registry('versioning')->getCurrentBranch());
		else
			$this->_headerText = $this->__('Commit history (%s)', Mage::getStoreConfig('versioning/scm/type'));

		$this->_removeButton('add');

		if (is_file($this->helper('versioning')->getHistoryFile())) {
			$this->_addButton('history', array(
				'label'   => $this->__('Show upgrades log'),
				'onclick' => "location.href = '".$this->getUrl('versioning/repository/history')."';",
				'class'   => 'go'
			));
		}

		if (is_file($this->helper('versioning')->getLastlogFile())) {
			$this->_addButton('lastlog', array(
				'label'   => $this->__('Show upgrade log'),
				'onclick' => "location.href = '".$this->getUrl('versioning/repository/lastlog')."';",
				'class'   => 'go'
			));
		}
	}
}