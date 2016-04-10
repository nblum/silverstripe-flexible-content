<?php

class ImageTextContentElement extends ContentElement
{

    public static $singular_name = 'Bild/Text Inhalt';

    public static $plural_name = 'Bild/Text Inhalte';

    private static $db = array(
        'Content' => 'HTMLText',
        'ImagePos' => 'VarChar(5)',
        'ImageSize' => 'VarChar(5)',
    );

    static $has_one = array(
        'Image' => 'Image'
    );


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $urlSegment = $this->Parent()->getField('URLSegment');

        if (empty($urlSegment)) {
            $fields->addFieldToTab('Root.Main', new LabelField('
            <strong>Bilder</strong><br />
            Der Beitrag muss gespeicher werden bevor Bilder angehängt werden können.
            '));

            $fields->addFieldToTab('Root.Main', new HiddenField('ImagePos'));
            $fields->addFieldToTab('Root.Main', new HiddenField('ImageSize'));
            $fields->addFieldToTab('Root.Main', new HiddenField('Image'));
        } else {
            $imagePos = new OptionsetField(
                'ImagePos',
                'Bild Position',
                [
                    'no' => 'Kein Bild',
                    'left' => 'Bild links vom Text',
                    'right' => 'Bild rechts vom Text',
                ],
                'no'
            );
            $fields->addFieldToTab('Root.Main', $imagePos);

            $imageSize = new OptionsetField(
                'ImageSize',
                'Bild Größe',
                [
                    'small' => 'Klein',
                    'thumb' => 'Vorschau (vergrößerbar)',
                    'full' => 'Originalgröße',
                ],
                'thumb'
            );
            $fields->addFieldToTab('Root.Main', $imageSize);
            $imageSize->hideUnless('ImagePos')->isNotEqualTo('no');

            $uploadField = new UploadField('Image', 'Bild');
            $uploadField->setAllowedMaxFileNumber(1);
            $uploadField->setFolderName('Uploads/' . $urlSegment);
            $fields->addFieldToTab('Root.Main', $uploadField);
        }

        $field = new HtmlEditorField('Content', 'Content');
        $field->setRows(15);
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }



    public function ContentShort()
    {
        return substr(strip_tags($this->getField('Content')), 0, 30) . '...';
    }

    public function ImagePreview()
    {
        return $this->Image()->SetHeight(40);
    }
}