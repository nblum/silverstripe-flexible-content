<?php


class TextContentElement extends \ContentElement
{
    private static $db = array(
        'Content' => 'HTMLText'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $field = new HtmlEditorField('Content', _t('ContentElement.content.name'));
        $field->setRows(15);
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }

    public function Preview()
    {
        return substr(strip_tags($this->getField('Content')), 0, 30) . '...';
    }
}