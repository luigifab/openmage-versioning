<?php
/**
 * Created V/06/04/2012
 * Updated S/23/10/2021
 *
 * Copyright 2011-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Versioning_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('versioning_history_grid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('desc');

		$this->setUseAjax(true);
		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(true);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {

		$page = $this->getParam($this->getVarNamePage(), 1);
		$size = $this->getParam($this->getVarNameLimit(), 20);

		$this->setCollection(Mage::getModel('versioning/history')->init($page, $size));
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('from', [
			'header'   => $this->__('Current revision'),
			'index'    => 'from',
			'align'    => 'center',
			'width'    => '130px',
			'sortable' => false,
			'filter'   => false
		]);

		$this->addColumn('to', [
			'header'   => $this->__('Requested revision'),
			'index'    => 'to',
			'align'    => 'center',
			'width'    => '130px',
			'sortable' => false,
			'filter'   => false
		]);

		$this->addColumn('branch', [
			'header'   => $this->__('Branch'),
			'index'    => 'branch',
			'align'    => 'center',
			'width'    => '130px',
			'sortable' => false,
			'filter'   => false
		]);

		$this->addColumn('remote_addr', [
			'header'   => $this->__('IP address'),
			'index'    => 'remote_addr',
			'align'    => 'center',
			'sortable' => false,
			'filter'   => false
		]);

		$this->addColumn('user', [
			'header'   => $this->__('User Name'),
			'index'    => 'user',
			'align'    => 'center',
			'sortable' => false,
			'filter'   => false
		]);

		$this->addColumn('date', [
			'header'   => $this->__('Date'),
			'index'    => 'date',
			'type'     => 'datetime',
			'format'   => Mage::getSingleton('core/locale')->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'align'    => 'center',
			'width'    => '150px',
			'sortable' => false,
			'filter'   => false
		]);

		$this->addColumn('duration', [
			'header'   => $this->__('Duration'),
			'index'    => 'duration',
			'align'    => 'center',
			'width'    => '60px',
			'sortable' => false,
			'filter'   => false,
			'frame_callback' => [$this, 'decorateDuration']
		]);

		$this->addColumn('status', [
			'header'    => $this->__('Status'),
			'index'     => 'status',
			'width'     => '125px',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => [$this, 'decorateStatus']
		]);

		$this->addColumn('action', [
			'type'      => 'action',
			'align'     => 'center',
			'width'     => '55px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
			'frame_callback' => [$this, 'decorateLink']
		]);

		return parent::_prepareColumns();
	}


	public function getRowClass($row) {
		return '';
	}

	public function getRowUrl($row) {
		return false;
	}


	public function decorateStatus($value, $row, $column, $isExport) {
		$status = in_array($row->getData('status'), ['Update completed', 'Upgrade completed']) ? 'success' : 'error';
		$text   = ($status == 'success') ? $this->helper('versioning')->_('Success') : $this->helper('versioning')->_('Error');
		return $isExport ? $text : sprintf('<span class="versioning-status grid-%s">%s</span>', $status, $text);
	}

	public function decorateDuration($value, $row, $column, $isExport) {
		return $this->helper('versioning')->getHumanDuration($row->getData('duration'));
	}

	public function decorateLink($value, $row, $column, $isExport) {
		return sprintf('<button type="button" class="slink" onclick="versioning.history(this, \'%s\');">%s</button>',
			base64_encode($row->getData('details')), $this->__('View'));
	}
}