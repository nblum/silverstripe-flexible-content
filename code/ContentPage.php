<?php

class ContentPage extends Page implements PermissionProvider
{
    const CONFIG = 'FlexibleContent';

    private static $singular_name = 'Inhalts Seite';

    static $has_many = array(
        'ContentElements' => 'ContentElement'
    );

    public function providePermissions()
    {
        return array(
            'FLEXIBLE_CONTENT_SORT' => array(
                'category' => 'Flexible Content',
                'name' => 'Can change the order of flexible content entries'
            )
        );
    }

    public function getCMSActions()
    {
        if (
            !Permission::checkMember(Member::currentUser(), 'FLEXIBLE_CONTENT_CREATE')
            && !Permission::checkMember(Member::currentUser(), 'FLEXIBLE_CONTENT_EDIT')
            && !Permission::checkMember(Member::currentUser(), 'FLEXIBLE_CONTENT_DELETE')
            && !Permission::checkMember(Member::currentUser(), 'FLEXIBLE_CONTENT_SORT')
        ) {
            return $actions = new FieldList();
        }

        $majorActions = CompositeField::create()->setName('MajorActions')->setTag('fieldset')->addExtraClass('ss-ui-buttonset');

        $majorActions->push(
            $publish = FormAction::create('publish', _t('SiteTree.BUTTONPUBLISHE', 'Publish'))
                ->setAttribute('data-icon', 'accept')
                ->setAttribute('data-icon-alternate', 'disk')
        );

        $actions = new FieldList(array($majorActions));
        $this->extend('updateCMSActions', $actions);

        return $actions;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldFromTab('Root.Main', 'Content');
        $fields->removeFieldFromTab('Root.Main', 'Metadata');


        $fields->addFieldToTab('Root.Main', new HeaderField('Seiten Inhalt'));

        $elementSelector = new GridFieldAddNewMultiClass();
        $elementSelector->setClasses($this->getAvailableClasses());

        $config = GridFieldConfig::create();
        $config->addComponent(new GridFieldButtonRow('before'));
        if (Permission::checkMember(Member::currentUser(), 'FLEXIBLE_CONTENT_EDIT')) {
            $config->addComponent(new GridFieldEditButton());
        }
        $config->addComponent(new GridFieldDetailForm());
        $config->addComponent($columns = new GridFieldDataColumns());
        $config->addComponent($elementSelector);
        if (Permission::checkMember(Member::currentUser(), 'FLEXIBLE_CONTENT_SORT')) {
            $config->addComponent(new GridFieldOrderableRows());
        }
        $config->addComponent(new GridFieldDeleteAction());

        $columns->setDisplayFields([
            'Name' => 'Name',
            'ClassName' => 'ClassName',
            'LastChange' => 'Changed',
            'ContentShort' => 'Description',
            'ImagePreview' => 'Image'
        ]);
        $columns->setFieldFormatting([
            'ClassName' => function ($name) {
                if (!class_exists($name)) {
                    return $name;
                }

                if (!property_exists($name, 'singular_name')) {
                    return $name;
                }
                return $name::$singular_name;
            }
        ]);

        $config->extend('updateConfig');
        $grid = new GridField(
            'ContentElement',
            'ContentElements',
            $this->ContentElements(),
            $config
        );

        $fields->addFieldToTab('Root.Main', $grid);
        return $fields;
    }

    /**
     * @return array|scalar
     */
    protected function getAvailableClasses()
    {
        $allowedClasses = Config::inst()->get(self::CONFIG, 'availableContentElements');
        if (is_array($allowedClasses)) {
            foreach ($allowedClasses as $key => $class) {
                if (!ClassInfo::exists($class)) {
                    unset($allowedClasses[$key]);
                }
            }

            return $allowedClasses;
        }

        $forbiddenClasses = Config::inst()->get(self::CONFIG, 'forbiddenContentElements');
        $classes = array_values(ClassInfo::subclassesFor('ContentElement'));
        if (is_array($forbiddenClasses)) {
            foreach ($forbiddenClasses as $class) {
                $key = array_search($class, $classes);
                if ($key !== false) {
                    unset($classes[$key]);
                }
            }
        }
        return $classes;
    }
}


class ContentPage_Controller extends Page_Controller
{

    private static $allowed_actions = array(
        'ContentElements'
    );

    /**
     * creates List of all rows with content
     * @return ArrayList
     */
    public function Elements()
    {
        return $this->ContentElements()->sort('Sort ASC');
    }
}