<?php

class ContentPage extends Page
{

    private static $singular_name = 'Inhalts Seite';

    static $has_many = array(
        'ContentElements' => 'ContentElement'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldFromTab('Root.Main', 'Content');
        $fields->removeFieldFromTab('Root.Main', 'Metadata');


        $fields->addFieldToTab('Root.Main', new HeaderField('Seiten Inhalt'));

        $config = GridFieldConfig::create();
        $config->addComponent(new GridFieldButtonRow('before'));
        $config->addComponent(new GridFieldEditButton());
        $config->addComponent(new GridFieldDeleteAction());
        $config->addComponent(new GridFieldDetailForm());
        $config->addComponent($columns = new GridFieldDataColumns());
        $config->addComponent(new GridFieldAddNewMultiClass());
        $config->addComponent(new GridFieldOrderableRows());

        $columns->setDisplayFields([
            'ClassName' => 'ClassName',
            'LastChange' => 'Changed',
            'Name' => 'Name',
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