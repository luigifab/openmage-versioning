<?php
/**
 * Created S/03/12/2011
 * Updated J/30/07/2020
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

class Luigifab_Versioning_Block_Adminhtml_Repository extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();
		$system = $this->helper('versioning')->getSystem();

		$this->_controller = 'adminhtml_repository';
		$this->_blockGroup = 'versioning';
		$this->_headerText = empty($branch = $system->getCurrentBranch()) ?
			$this->__('Revisions history (%s)', '<span id="scmtype">'.$system->getType().'</span>') :
			$this->__('Revisions history (%s, %s)', '<span id="scmtype">'.$system->getType().'</span>', $branch);

		$this->_removeButton('add');

		$this->_addButton('diff', [
			'label'   => 'diff',
			'title'   => $this->__('Show diff'),
			'onclick' => "versioning.goDiff('".$this->getUrl('*/*/status', ['from' => 'abc', 'to' => 'abc'])."');",
			'class'   => 'go'
		]);

		$this->_addButton('log', [
			'label'   => 'log',
			'title'   => $this->__('Updates history'),
			'onclick' => "setLocation('".$this->getUrl('*/*/history')."');",
			'class'   => 'go'
		]);

		$this->_addButton('status', [
			'label'   => 'status',
			'title'   => $this->__('Repository status'),
			'onclick' => "setLocation('".$this->getUrl('*/*/status')."');",
			'class'   => 'go'
		]);

		if (is_file($this->helper('versioning')->getMaintenanceFlag())) {
			$this->_addButton('maintenance_flag', [
				'label'   => $this->__('Remove the maintenance page'),
				'onclick' => "versioning.cancelFlag('".$this->getUrl('*/*/delMaintenanceFlag')."');",
				'class'   => 'delpage delete'
			]);
		}
		else {
			$this->_addButton('maintenance_flag', [
				'label'   => $this->__('Enable the maintenance page'),
				'onclick' => "versioning.confirmFlag('".$this->getUrl('*/*/addMaintenanceFlag')."', this.textContent, '".$this->helper('versioning')->getMaintenanceInfo()."');"
			]);
		}

		if (is_file($this->helper('versioning')->getUpgradeFlag())) {
			$this->_addButton('upgrade_flag', [
				'label'   => $this->__('Remove the update page'),
				'onclick' => "versioning.cancelFlag('".$this->getUrl('*/*/delUpgradeFlag')."');",
				'class'   => 'delpage delete'
			]);
		}
		else {
			$this->_addButton('upgrade_flag', [
				'label'   => $this->__('Enable the update page'),
				'onclick' => "versioning.confirmFlag('".$this->getUrl('*/*/addUpgradeFlag')."', this.textContent, '".$this->helper('versioning')->getUpgradeInfo()."');"
			]);
		}
	}

	public function getGridHtml() {

		$commits = $this->helper('versioning')->getSystem()->getCommitsCollection();
		$columns = $commits->getColumnValues('column'); sort($columns);
		$json = [];
		$cnt  = count($commits) - 1;

		// comptage dans l'ordre inverse
		// le commit le plus récent = count($commits) - 1
		// le commit le plus ancien = 0
		foreach ($commits as $commit) {
			$revision = $commit->getData('revision');
			$json[$revision] = [
				'revision' => $revision,
				'parents'  => $commit->getData('parents'),
				'branch'   => $commit->getData('branch'),
				'col'      => $commit->getData('column'),
				'row'      => $cnt--
			];
		}

		return $this->getChildHtml('grid')."\n".
			'<script type="text/javascript">'."\n".
			'self.versioningIds = '.json_encode($json).";\n".
			'self.versioningCols = '.array_pop($columns).";\n".
			'self.versioningText = '.json_encode([$this->helper('versioning')->getFields(), $this->__('Martian sunset seen by Spirit.')]).";\n".
			'</script>';
	}

	public function getHeaderCssClass() {
		return 'icon-head '.parent::getHeaderCssClass().' '.$this->helper('versioning')->getSystem()->getType();
	}
}