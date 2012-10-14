<?php
/**
 * Created S/02/06/2012
 * Updated V/12/10/2012
 * Version 8
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

class Luigifab_Versioning_Block_Adminhtml_Downtime extends Mage_Adminhtml_Block_Widget_View_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_downtime';
		$this->_blockGroup = 'versioning';

		$this->_headerText = $this->__('Downtime');
		$this->_removeButton('edit');
		$this->_removeButton('back');

		if (Mage::getSingleton('admin/session')->isAllowed('tools/versioning')) {
			$this->_addButton('versioning', array(
				'label'   => $this->__('Versioning'),
				'onclick' => "location.href = '".$this->getUrl('*/versioning_repository/index')."';",
				'class'   => 'back'
			));
		}

		$this->_addButton('config', array(
			'label'   => $this->__('Configuration'),
			'onclick' => "location.href = '".$this->getUrl('adminhtml/system_config/edit', array('section' => 'versioning'))."';",
			'class'   => 'go'
		));
	}

	public function getViewHtml() {

		$html = array();
		$html[] = '<ul id="downtime">';

		if (!is_file(Mage::helper('versioning')->getMaintenanceFlag())) {
			$html[] = '<li>';
			$html[] = '<h3>maintenance.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <em>not present</em>: <a href="%s">lock website</a>.', $this->getUrl('*/*/addMaintenanceFlag')).'</p>';
			$html[] = '</li>';
		}
		else {
			$html[] = '<li>';
			$html[] = '<h3>maintenance.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <strong>present</strong>: <a href="%s">unlock website</a>.', $this->getUrl('*/*/delMaintenanceFlag')).'</p>';
			$html[] = '</li>';
		}

		if (!is_file(Mage::helper('versioning')->getUpgradeFlag())) {
			$html[] = '<li>';
			$html[] = '<h3>upgrade.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <em>not present</em>: <a href="%s">lock website</a>.', $this->getUrl('*/*/addUpgradeFlag')).'</p>';
			$html[] = '</li>';
		}
		else {
			$html[] = '<li>';
			$html[] = '<h3>upgrade.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <strong>present</strong>: <a href="%s">unlock website</a>.', $this->getUrl('*/*/delUpgradeFlag')).'</p>';
			$html[] = '</li>';
		}

		$html[] = '</ul>';

		return implode("\n", $html);
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}