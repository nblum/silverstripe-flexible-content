<?php

class ImageContentElement extends ContentElement
{

    public static $singular_name = 'Bild Inhalt';

    public static $plural_name = 'Bild Inhalte';

    private static $db = array(
        'Caption' => 'HTMLText'
    );

    static $has_one = array(
        'Image' => 'Image'
    );

    public function ImageSize() {
        return 'full-width';
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $urlSegment = $this->Parent()->getField('URLSegment');
        $uploadField = new UploadField('Image', 'Bild');
        $uploadField->setAllowedMaxFileNumber(1);
        $uploadField->setFolderName('Uploads/' . $urlSegment);
        $uploadField->setAllowedExtensions(['jpg', 'png']);
        if (empty($urlSegment)) {
            $fields->addFieldToTab('Root.Main', new LabelField('Image', '
            <strong>Bild anhängen</strong><br />
            Der Beitrag muss gespeicher werden bevor Bilder angehängt werden können
            '));
        } else {
            $fields->addFieldToTab('Root.Main', $uploadField);
        }

        $field = new TextareaField('Caption', 'Caption');
        $field->setRows(5);
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }

    public function ContentShort()
    {
        return substr(strip_tags($this->getField('Caption')), 0, 30) . '...';
    }

    public function ImagePreview()
    {
        return $this->Image()->SetHeight(40);
    }
}