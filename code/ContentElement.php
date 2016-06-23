<?php

class ContentElement extends DataObject implements PermissionProvider
{

    public static $singular_name = 'Überschrift';

    public static $plural_name = 'Überschriften';

    private static $db = array(
        'Name' => 'VarChar(100)',
        'Title' => 'VarChar(100)',
        'Sort' => 'Int',
        'Active' => 'Boolean',
        'Changed' => 'SS_Datetime'
    );

    private static $has_one = array(
        'Parent' => 'Page'
    );

    public function providePermissions()
    {
        return array(
            'FLEXIBLE_CONTENT_CREATE' => array(
                'category' => 'Flexible Content',
                'name' => 'Can create new flexible content entries'
            ),
            'FLEXIBLE_CONTENT_EDIT' => array(
                'category' => 'Flexible Content',
                'name' => 'Can edit flexible content entries'
            ),
            'FLEXIBLE_CONTENT_DELETE' => array(
                'category' => 'Flexible Content',
                'name' => 'Can delete flexible content entries'
            )
        );
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return true;
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canCreate($member = null)
    {
        return Permission::checkMember($member, 'FLEXIBLE_CONTENT_CREATE');
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return Permission::checkMember($member, 'FLEXIBLE_CONTENT_EDIT');
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return Permission::checkMember($member, 'FLEXIBLE_CONTENT_DELETE');
    }

    /**
     * @inheritdoc
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', HiddenField::create('Changed'));
        $fields->addFieldToTab('Root.Main', HiddenField::create('Sort'));
        $fields->addFieldToTab('Root.Main', HiddenField::create('ParentID'));

        $fields->addFieldsToTab('Root.Main', array(
            HeaderField::create('Info'),
            FieldGroup::create(
                LabelField::create('<strong>Letzte Änderung:</strong>'),
                LabelField::create($this->LastChange())
            ),
            FieldGroup::create(
                LabelField::create('<strong>Aktuelle Position:</strong>'),
                LabelField::create($this->getField('Sort'))
            )
        ));

        $fields->addFieldToTab('Root.Main', HeaderField::create('Inhalt'));

        $field = new CheckboxField('Active', 'Aktiv');
        $field->setDescription('Abschnitt anzeigen');
        $fields->addFieldToTab('Root.Main', $field);

        $field = new TextField('Title', 'Title');
        $field->setDescription('Überschrift des Abschnitts');
        $fields->addFieldToTab('Root.Main', $field);

        $field = new TextField('Name', 'Name');
        $field->setDescription('Nicht auf der Webseite sichtbar');
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }

    /**
     * @inheritdoc
     */
    protected function onBeforeWrite()
    {
        //set the initial sort order
        if ((int)$this->Sort === 0) {
            $this->Sort = (int)ContentElement::get(get_class($this))->max('Sort') + 1;
        }

        //set name to title if empty
        if (empty($this->Name)) {
            $name = !empty($this->Title) ? $this->Title : 'No Name';
            $this->Name = substr($name, 0, 30);
        }

        //update change date
        $this->Changed = date('Y-m-d H:i:s');

        parent::onBeforeWrite();
    }

    /**
     * returns a readable last change/edit date
     * @return bool|string
     */
    public function LastChange()
    {
        $today = new DateTime(); // This object represents current date/time
        $today->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->Changed);
        if (!($dateTime instanceof DateTime)) {
            return '---';
        }

        $todayTime = $dateTime;
        $todayTime->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $diff = $today->diff($todayTime);
        $diffDays = (integer)$diff->format('%R%a'); // Extract days count in interval

        switch ($diffDays) {
            case 0:
                return 'Heute ' . date('H:i', strtotime($this->Changed));
                break;
            case -1:
                return 'Gestern ' . date('H:i', strtotime($this->Changed));
                break;
            default:
                return date('j. M H:i', strtotime($this->Changed));
        }
    }
}
