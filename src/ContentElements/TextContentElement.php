<?php

namespace Nblum\FlexibleContent\ContentElements;

use Nblum\FlexibleContent\ContentElement;
use Nblum\FlexibleContent\IContentElement;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

class TextContentElement extends ContentElement implements IContentElement
{
    private static $table_name = 'TextContentElement';

    private static $singular_name = 'Text';

    private static $plural_name = 'Text';

    private static $db = array(
        'Content' => 'HTMLText'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $field = new HTMLEditorField('Content', _t('ContentElement.content.name'));
        $field->setRows(15);
        $fields->addFieldToTab('Root.Main', $field);
        return $fields;
    }

    public function Preview()
    {
        return substr(strip_tags($this->getField('Content')), 0, 30) . '...';
    }
}
