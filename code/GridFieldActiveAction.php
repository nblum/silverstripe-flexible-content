<?php

namespace Nblum\FlexibleContent;

/**
 * This class is a {@link GridField} component that adds a delete action for
 * objects.
 *
 * This component also supports unlinking a relation instead of deleting the
 * object.
 *
 * Use the {@link $removeRelation} property set in the constructor.
 *
 * <code>
 * $action = new GridFieldDeleteAction(); // delete objects permanently
 *
 * // removes the relation to object instead of deleting
 * $action = new GridFieldDeleteAction(true);
 * </code>
 *
 * @package forms
 * @subpackage fields-gridfield
 */
class GridFieldActiveAction implements \GridField_ColumnProvider , \GridField_ActionProvider {

    /**
     *
     * @param boolean $removeRelation - true if removing the item from the list, but not deleting it
     */
    public function __construct() {
    }

    /**
     * Add a column 'Delete'
     *
     * @param GridField $gridField
     * @param array $columns
     */
    public function augmentColumns($gridField, &$columns) {
        if(!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    /**
     * Return any special attributes that will be used for FormField::create_tag()
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName) {
        return array('class' => 'col-buttons');
    }

    /**
     * Add the title
     *
     * @param GridField $gridField
     * @param string $columnName
     * @return array
     */
    public function getColumnMetadata($gridField, $columnName) {
        if($columnName == 'Actions') {
            return array('title' => '');
        }
    }

    /**
     * Which columns are handled by this component
     *
     * @param GridField $gridField
     * @return array
     */
    public function getColumnsHandled($gridField) {
        return array('Actions');
    }

    /**
     * Which GridField actions are this component handling
     *
     * @param GridField $gridField
     * @return array
     */
    public function getActions($gridField) {
        return array('toggle-active');
    }

    /**
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return string - the HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName) {

        $active = $record->getField('Active') ? 'active' : 'inactive';

        $field = \GridField_FormAction::create($gridField,  'ToggleActiveRecord'.$record->ID, false, "toggle-active",
            array('RecordID' => $record->ID))
            ->addExtraClass('flexible-content-btn flexible-content-button-' . $active)
            ->setAttribute('title', _t('GridAction.Active', "Active"))
            ->setDescription(_t('GridAction.TOGGLE_ACTIVE_DESCRIPTION','Toggle Active'));
        return $field->Field();
    }

    /**
     * Handle the actions and apply any changes to the GridField
     *
     * @param \GridField $gridField
     * @param string $actionName
     * @param mixed $arguments
     * @param array $data - form data
     * @return void
     */
    public function handleAction(\GridField $gridField, $actionName, $arguments, $data) {
        if($actionName == 'toggle-active') {
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if(!$item) {
                return;
            }

            if($item->getField('Active')) {
                $item->setField('Active', 0);
            } else {
                $item->setField('Active', 1);
            }
            $item->write();
        }
    }
}
