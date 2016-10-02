<?php

class ContentElement extends \DataObject implements \PermissionProvider
{
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
                'category' => _t('FlexibleContent.permission.category'),
                'name' => _t('ContentElement.permission.create.name'),
            ),
            'FLEXIBLE_CONTENT_EDIT' => array(
                'category' => _t('FlexibleContent.permission.category'),
                'name' => _t('ContentElement.permission.edit.name'),
            ),
            'FLEXIBLE_CONTENT_DELETE' => array(
                'category' => _t('FlexibleContent.permission.category'),
                'name' => _t('ContentElement.permission.delete.name'),
            )
        );
    }

    private static $extensions = [
        'Nblum\FlexibleContent\FlexibleContentVersionedDataObject'
    ];

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
        return \Permission::checkMember($member, 'FLEXIBLE_CONTENT_CREATE');
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return \Permission::checkMember($member, 'FLEXIBLE_CONTENT_EDIT');
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return \Permission::checkMember($member, 'FLEXIBLE_CONTENT_DELETE');
    }


    /**
     * @inheritdoc
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', \HiddenField::create('Changed'));
        $fields->addFieldToTab('Root.Main', \HiddenField::create('Sort'));
        $fields->addFieldToTab('Root.Main', \HiddenField::create('ParentID'));

        $fields->addFieldsToTab('Root.Main', array(
            \HeaderField::create('Info'),
            \FieldGroup::create(
                \LabelField::create('<strong>Letzte Änderung:</strong>'),
                \LabelField::create($this->LastChange())
            ),
            \FieldGroup::create(
                \LabelField::create('<strong>Aktuelle Position:</strong>'),
                \LabelField::create($this->getField('Sort'))
            )
        ));

        $fields->addFieldToTab('Root.Main', \HeaderField::create(_t('ContentElement.header')));

        $field = new \CheckboxField('Active', _t('ContentElement.active.name'));
        $field->setDescription(_t('ContentElement.active.description'));
        $fields->addFieldToTab('Root.Main', $field);

        $field = new \TextField('Title', _t('ContentElement.title.name'));
        $field->setDescription(_t('ContentElement.title.description'));
        $fields->addFieldToTab('Root.Main', $field);

        $field = new \TextField('Name', _t('ContentElement.name.name'));
        $field->setDescription(_t('ContentElement.name.description'));
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }

    /**
     * @return int
     */
    protected function getMaxSort()
    {
        $results = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::get_by_stage(
            \ContentElement::class,
            'Stage'
            , [
            'Active' => '1'
        ], [
            'Sort' => 'DESC'
        ],
            '',
            '1');

        if (!$results || $results->count() === 0) {
            return 0;
        }

        return (int)$results->first()->getField('Sort');
    }

    /**
     * @inheritdoc
     */
    protected function onBeforeWrite()
    {
        //set the initial sort order
        if ((int)$this->Sort === 0) {
            $maxSort = $this->getMaxSort();
            $this->Sort = $maxSort + 1;
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


    public function getSingularName()
    {
        return $this->i18n_singular_name();
    }

    public function Preview()
    {
        return '';
    }

    /**
     * returns a readable last change/edit date
     * @return bool|string
     */
    public function LastChange()
    {
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

    public function PublishState()
    {
        $latest = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::get_latest_version(self::class, $this->getField('ID'));
        if ($latest->record['WasPublished'] && $latest->isPublished()) {

            return sprintf(
                '<span class="publish-state published" title="%s"></span>',
                _t('ContentElement.state.published', 'Published version {current}', [
                    'current' => $latest->record['Version'],
                ])
            );
        }

        $versions = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::get_all_versions(self::class, $this->getField('ID'));
        foreach ($versions as $version) {
            if ($version->record['WasPublished']) {

                return sprintf(
                    '<span class="publish-state published-old" title="%s"></span>',
                    _t('ContentElement.state.publishedOld', 'Published version {current} (Edited: {time})', [
                        'current' => $version->record['Version'],
                        'time' => $latest->record['LastEdited']
                    ])
                );
            }
        }

        return sprintf(
            '<span class="publish-state unpublished" title="%s"></span>',
            _t('ContentElement.state.unpublished', 'Unpublished')
        );
    }

    /**
     * creates a readable (page) unique identifier for the current content element
     */
    public function getReadableIdentifier()
    {
        return sprintf(
            '%s%s',
            $this->getField('ID'),
            !empty(trim($this->getField('Title'))) ? '-' . urlencode(strtolower(trim($this->getField('Title')))) : ''
        );
    }

    /**
     * returns all content element of the same page
     * @param string $active
     * @return DataList
     */
    public function getSiblings($active = '1')
    {
        $stage = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::get_live_stage();
        $results = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::get_by_stage(
            \ContentElement::class,
            $stage
            , [
            'Active' => $active
        ], [
            'Sort' => 'ASC'
        ]);

        return $results;
    }


    /**
     * @return \HTMLText
     *
     */
    public function forTemplate()
    {
        $template = $this->getClassName();

        if (\SSViewer::hasTemplate($template)) {
            return $this->renderWith($template);
        }

        return _t('ContentElement.missingTemplate', 'Missing Template for {template}', [
            'template' => $template
        ]);
    }
}
