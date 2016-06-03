<?php

class TextContentElement extends ContentElement
{

    public static $singular_name = 'Text Inhalt';

    public static $plural_name = 'Text Inhalte';

    private static $db = array(
        'Content' => 'HTMLText'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $field = new HtmlEditorField('Content', 'Content');
        $field->setRows(15);
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }

    public function ContentShort()
    {
        return substr(strip_tags($this->getField('Content')), 0, 30) . '...';
    }
}