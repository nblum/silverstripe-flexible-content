<?php

namespace Nblum\FlexibleContent;

/**
 * This component provides a button for copying record.
 * First of all it dublicates record and then opens opens edit form {@link GridFieldDetailForm}.
 *
 * @package framework
 * @subpackage gridfield
 * @author Elvinas Liutkevičius <elvinas@unisolutions.eu>
 * @license BSD http://silverstripe.org/BSD-license
 */
class GridFieldCopyToSessionButton implements \GridField_ColumnProvider , \GridField_ActionProvider  {

	public function augmentColumns($gridField, &$columns) {
		if(!in_array('Actions', $columns))
			$columns[] = 'Actions';
	}

	public function getColumnAttributes($gridField, $record, $columnName) {
		return array('class' => 'col-buttons');
	}

	public function getColumnMetadata($gridField, $columnName) {
		if($columnName == 'Actions') {
			return array('title' => '');
		}
	}

	public function getColumnsHandled($gridField) {
		return array('Actions');
	}

	public function getActions($gridField) {
		return array('copyrecord');
	}

	public function getColumnContent($gridField, $record, $columnName) {
		if(!$record->canCreate()) return;
		$field = \GridField_FormAction::create($gridField,  'CopyRecord'.$record->ID, false, "copyrecord",
				array('RecordID' => $record->ID))
			->addExtraClass('flexible-content-btn')
			->addExtraClass('flexible-content-button-copy')
			->setAttribute('title', _t('GridAction.Copy', "Copy"))
			->setDescription(_t('GridAction.COPY_DESCRIPTION','Copy'));

		return $field->Field();
	}

	public function handleAction(\GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'copyrecord'){
			$item = $gridField->getList()->byID($arguments['RecordID']);

            //add session entry for handling paste
            $_SESSION['flexible-content']['copy'] = json_encode([
                'id' => $arguments['RecordID'],
                'title' => $item->getField('Name')
            ]);
		}
	}

}
