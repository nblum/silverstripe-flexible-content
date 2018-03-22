<?php

namespace Nblum\FlexibleContent;

use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LabelField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\SSViewer;

/**
 * Class ContentElement
 * @package Nblum\FlexibleContent
 */
class ContentElement extends DataObject
{
    private static $table_name = 'ContentElement';

    private static $extensions = [
        Versioned::class
    ];

    /**
     * versioning is handled by the holder page
     */
    private static $versioned_gridfield_extensions = false;

    private static $db = array(
        'Name' => 'Varchar(100)'
    );

    private static $has_one = array(
        'Parent' => ContentPage::class
    );


    /**
     * @inheritdoc
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', HiddenField::create('ParentID'));
        $fields->addFieldsToTab('Root.Main', array(
            HeaderField::create('Info', 'Info'),
            FieldGroup::create(
                LabelField::create('<strong>Letzte Änderung:</strong>'),
                LabelField::create($this->LastChange())
            )
        ));
        $fields->addFieldToTab('Root.Main', HeaderField::create('ContentElement.header', _t('ContentElement.header')));
        $field = new TextField('Title', _t('ContentElement.title.name'));
        $field->setDescription(_t('ContentElement.title.description'));
        $fields->addFieldToTab('Root.Main', $field);
        $field = new TextField('Name', _t('ContentElement.name.name'));
        $field->setDescription(_t('ContentElement.name.description'));
        $fields->addFieldToTab('Root.Main', $field);
        return $fields;
    }


    /**
     * @inheritdoc
     */
    protected function onBeforeWrite()
    {
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
     * @return DBHTMLText
     */
    public function forTemplate()
    {
        $template = $this->getClassName();
        if (SSViewer::hasTemplate($template)) {
            return $this->renderWith($template);
        }
        throw new \RuntimeException(
            _t('ContentElement.missingTemplate', 'Missing Template for {template}', [
                'template' => $template
            ])
        );
    }


    /**
     * returns a readable last change/edit date
     * @return string|null
     */
    public function LastChange()
    {
        if (!$this->Changed) {
            return '---';
        }
        $today = new \DateTime(); // This object represents current date/time
        $today->setTime(0, 0, 0); // reset time part, to prevent partial comparison
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->Changed);
        if (!($dateTime instanceof \DateTime)) {
            return '---';
        }
        $todayTime = $dateTime;
        $todayTime->setTime(0, 0, 0); // reset time part, to prevent partial comparison
        $diff = $today->diff($todayTime);
        $diffDays = (integer)$diff->format('%R%a'); // Extract days count in interval
        switch ($diffDays) {
            case 0:
                return _t('ContentElement.today', 'Today') . ' ' . date('H:i', strtotime($this->Changed));
                break;
            case -1:
                return _t('ContentElement.yesterday', 'Yesterday') . ' ' . date('H:i', strtotime($this->Changed));
                break;
            default:
                return date('j. M H:i', strtotime($this->Changed));
        }
    }

    public function Type()
    {
        return $this->singular_name();
    }

    public function PublishState()
    {
        try {
            $latestVersionObj = Versioned::get_latest_version(self::class, $this->getField('ID'));
            $latestVersion = $latestVersionObj->getField('Version');
            $liveVersionNumber = Versioned::get_versionnumber_by_stage(self::class, Versioned::LIVE, $this->getField('ID'));
            $liveVersion = Versioned::get_one_by_stage(self::class, Versioned::LIVE, "ID=" . (int)$liveVersionNumber);
        } catch (\Exception $e) {
            return '---';
        }

        if ($latestVersion === $liveVersionNumber) {
            return _t('ContentElement.state.published', 'published');
        }
        if (!$liveVersionNumber) {
            return _t('ContentElement.state.unpublished', 'unpublished');
        }
        if ($liveVersion) {
            return _t(
                'ContentElement.state.publishedOld',
                '{time}',
                [
                    'time' => $liveVersion->getField('LastEdited')
                ]
            );
        }

        return '---';
    }

    public function GridPreview()
    {
        return sprintf(
            '
            <div class="grid-preview" style="height:100px;">
                <div class="name">%1$s</div>
                <div class="type">&lt;%2$s&gt;</div>
                <div class="preview">%3$s</div>
                <div class="publish">%4$s</div>
            </div>',
            $this->getField('Name'),
            $this->Type(),
            $this->Preview(),
            $this->PublishState()
        );
    }
}
