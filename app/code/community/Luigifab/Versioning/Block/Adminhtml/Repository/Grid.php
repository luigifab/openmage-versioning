<?php
/**
 * Created S/03/12/2011
 * Updated S/11/04/2015
 * Version 40
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

class Luigifab_Versioning_Block_Adminhtml_Repository_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('versioning_grid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('DESC');

		$this->setUseAjax(false);
		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {

		$this->setCollection(Mage::registry('versioning')->getCommitCollection());
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('revision', array(
			'header'    => $this->__('Revision'),
			'index'     => 'revision',
			'align'     => 'center',
			'width'     => '85px',
			'filter'    => false,
			'sortable'  => false,
			'column_css_class' => 'revision',
			'frame_callback' => array($this, 'decorateRevision')
		));

		$this->addColumn('graph', array(
			'header'    => $this->__('Graph'),
			'width'     => '200px',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('author', array(
			'header'    => $this->__('Author'),
			'index'     => 'author',
			'align'     => 'center',
			'width'     => '185px',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('description', array(
			'header'    => $this->helper('adminhtml')->__('Description'),
			'index'     => 'description',
			'align'     => 'left',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => array($this, 'decorateDescription')
		));

		$this->addColumn('date', array(
			'header'    => $this->helper('adminhtml')->__('Date'),
			'index'     => 'date',
			'type'      => 'datetime',
			'align'     => 'center',
			'width'     => '185px',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('action', array(
			'type'    => 'action',
			'getter'  => 'getRevision',
			'actions' => array(
				array(
					'caption' => $this->__('Deliver'),
					'url'     => array('base' => '*/*/upgrade'),
					'field'   => 'revision',
					'onclick' => 'return versioning.confirmUpgrade(this.href, "'.addslashes($this->__('Upgrade to revision %s', '§')).'", "'.addslashes($this->helper('versioning')->getFields(true)).'", "'.addslashes($this->__('Martian sunset. Spirit at Gusev crater.')).'");'
				)
			),
			'align'     => 'center',
			'width'     => '55px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true
		));

		return parent::_prepareColumns();
	}


	public function getRowClass($row) {
		return '';
	}

	public function getRowUrl($row) {
		return null;
	}

	public function decorateDescription($value, $row, $column, $isExport) {

		$bugtracker = trim(Mage::getStoreConfig('versioning/scm/bugtracker'));
		$description = nl2br($row->getData('description'));

		if (strlen($bugtracker) > 0) {
			$description = preg_replace('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/', '<a href="$1" onclick="window.open(this.href); return false;">$1</a>', $description);
			$description = preg_replace('#\#([0-9]+)#', '<a href="'.$bugtracker.'$1" class="issue" onclick="window.open(this.href); return false;">$1</a>', $description);
		}
		else {
			$description = preg_replace('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/', '<a href="$1" onclick="window.open(this.href); return false;">$1</a>', $description);
		}

		return $description;
	}

	public function decorateRevision($value, $row, $column, $isExport) {

		$revision = $row->getData('revision');
		return ($revision === $row->getData('current_revision')) ? '<strong>'.$revision.'</strong>' : $revision;
	}
}