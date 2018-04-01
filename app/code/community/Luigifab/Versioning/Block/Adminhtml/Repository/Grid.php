<?php
/**
 * Created S/03/12/2011
 * Updated M/27/02/2018
 *
 * Copyright 2011-2018 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://www.luigifab.info/magento/versioning
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

class Luigifab_Versioning_Block_Adminhtml_Repository_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('versioning_grid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('desc');

		$this->setUseAjax(false);
		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {
		$this->setCollection(Mage::registry('versioning')->getCommitsCollection());
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('revision', array(
			'header'    => $this->__('Revision'),
			'index'     => 'revision',
			'align'     => 'center',
			'width'     => '100px',
			'filter'    => false,
			'sortable'  => false,
			'column_css_class' => 'revision',
			'frame_callback'   => array($this, 'decorateRevision')
		));

		$this->addColumn('diff', array(
			'header'    => $this->__('Diff'),
			'align'     => 'center',
			'width'     => '55px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
			'frame_callback' => array($this, 'decorateDiff')
		));

		$this->addColumn('graph', array(
			'header'    => $this->__('Graph'),
			'width'     => '200px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true
		));

		$this->addColumn('author', array(
			'header'    => $this->__('Author'),
			'width'     => '200px',
			'index'     => 'author',
			'align'     => 'center',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('description', array(
			'header'    => $this->__('Description'),
			'index'     => 'description',
			'align'     => 'left',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => array($this, 'decorateDescription')
		));

		$this->addColumn('date', array(
			'header'    => $this->__('Date'),
			'index'     => 'date',
			'type'      => 'datetime',
			'format'    => Mage::getSingleton('core/locale')->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'align'     => 'center',
			'width'     => '150px',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('action', array(
			'type'      => 'action',
			'align'     => 'center',
			'width'     => '85px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
			'frame_callback' => array($this, 'decorateLink')
		));

		return parent::_prepareColumns();
	}


	public function getRowClass($row) {
		return '';
	}

	public function getRowUrl($row) {
		return false;
	}

	public function canDisplayContainer() {
		return false;
	}

	public function getMessagesBlock() {
		return Mage::getBlockSingleton('core/template');
	}


	public function decorateRevision($value, $row, $column, $isExport) {
		return ($value == $row->getData('current_revision')) ? sprintf('<strong>%s</strong>', $value) : $value;
	}

	public function decorateDiff($value, $row, $column, $isExport) {
		return sprintf('<input type="radio" name="d1" value="%s" /> <input type="radio" name="d2" value="%1$s" />', $row->getData('revision'));
	}

	public function decorateDescription($value, $row, $column, $isExport) {

		$bugtracker  = Mage::getStoreConfig('versioning/scm/bugtracker');
		$description = nl2br($row->getData('description'));

		if (!empty($bugtracker)) {
			$description = preg_replace('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/', '<a href="$1" class="linkext">$1</a>', $description);
			$description = preg_replace('#\#([0-9]+)#', '<a href="'.$bugtracker.'$1" class="issue">$1</a>', $description);
		}
		else {
			$description = preg_replace('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/', '<a href="$1" class="linkext">$1</a>', $description);
		}

		return $description;
	}

	public function decorateLink($value, $row, $column, $isExport) {

		$url = $this->getUrl('*/*/upgrade', array('revision' => $row->getData('revision')));

		return sprintf('<button type="button" onclick="versioning.confirmUpgrade(\'%s\', \'%s\');">%s</button>',
			$url, $this->__('Update to revision %s', '§'), $this->__('Deliver'));
	}


	public function _toHtml() {
		return str_replace('class="data', 'class="data k k0', parent::_toHtml());
	}
}