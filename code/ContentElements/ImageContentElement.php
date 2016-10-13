<?php


class ImageContentElement extends \ContentElement
{
    private static $db = array(
        'Caption' => 'HTMLText'
    );

    public static $has_one = array(
        'Image' => 'Image'
    );

    public function ImageSize()
    {
        return 'full-width';
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $urlSegment = $this->Parent()->getField('URLSegment');
        $uploadField = new \UploadField('Image', _t('ContentElement.image.name'));
        $uploadField->setAllowedMaxFileNumber(1);
        $uploadField->setFolderName('Uploads/' . $urlSegment);
        $uploadField->setAllowedExtensions(['jpg', 'png']);
        $fields->addFieldToTab('Root.Main', $uploadField);
        $field = new \TextareaField('Caption', _t('ContentElement.image.caption'));
        $field->setRows(5);
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }

    public function Preview()
    {
        $url = '';

        if ($this->Image() !== null && $this->Image()->getURL()) {
            $url = $this->Image()->getURL();
        }

        $caption = substr(strip_tags($this->getField('Caption')), 0, 15) . '...';
        return sprintf('<img src="%s" style="height:30px; float: left; margin: 0 5px 5px 0" /> %s', $url, $caption);
    }
}