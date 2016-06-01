<?php

class ContentElement extends DataObject implements Searchable
{

    public static $singular_name = 'Überschrift';

    public static $plural_name = 'Überschriften';

    private static $db = array(
        'Name' => 'VarChar(100)',
        'Title' => 'VarChar(100)',
        'Sort' => 'Int',
        'Changed' => 'SS_Datetime'
    );

    private static $has_one = array(
        'Parent' => 'Page'
    );

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
    public function canEdit($member = null)
    {
        return true;
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return true;
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canCreate($member = null)
    {
        return true;
    }


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

        $field = new TextField('Name', 'Name');
        $field->setDescription('Nicht auf der Webseite sichtbar');
        $fields->addFieldToTab('Root.Main', $field);

        $field = new TextField('Title', 'Title');
        $field->setDescription('Überschrift des Abschnitts');
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }

    public function LastChange()
    {
        $today = new DateTime(); // This object represents current date/time
        $today->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $this->Changed);
        if (!($dateTime instanceof DateTime)) {
            return '---';
        }

        $todayTime = $dateTime;
        $todayTime->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $diff = $today->diff($todayTime);
        $diffDays = (integer)$diff->format("%R%a"); // Extract days count in interval

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

//    public function getCMSValidator()
//    {
//        //return new RequiredFields('Name');
//    }


    protected
    function onBeforeWrite()
    {
        if ((int)$this->Sort === 0) {
            $this->Sort = (int)ContentElement::get(get_class($this))->max('Sort') + 1;
        }

        if (empty($this->Name)) {
            $name = !empty($this->Title) ? $this->Title : 'No Name';
            $this->Name = substr($name, 0, 30);
        }

        $this->Changed = date("Y-m-d H:i:s");

        parent::onBeforeWrite();
    }

    /**
     * Link to this DO
     * @return string
     */
    public
    function Link()
    {
        return $this->Parent()->Link();
    }

    /**
     * Filter array
     * eg. array('Disabled' => 0);
     * @return array
     */
    public
    static function getSearchFilter()
    {
        return array();
    }

    /**
     * FilterAny array (optional)
     * eg. array('Disabled' => 0, 'Override' => 1);
     * @return array
     */
    public
    static function getSearchFilterAny()
    {
        return array();
    }

    /**
     * Fields that compose the Title
     * eg. array('Title', 'Subtitle');
     * @return array
     */
    public
    function getTitleFields()
    {
        return array('Title');
    }

    /**
     * Fields that compose the Content
     * eg. array('Teaser', 'Content');
     * @return array
     */
    public
    function getContentFields()
    {
        return array('Content');
    }
}
