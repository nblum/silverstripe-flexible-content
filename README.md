# silverstripe-flexible-content
Content pages based on data objects for better content structure

## Installation

```sh
composer require nblum/silverstripe-flexible-content
```

## Configuration

Edit your config.yml file and add the following lines:
```yml

FlexibleContent:
 #white listed conten elements
  availableContentElements:
    - TextContentElement
    - CodeContentElement
    - ImageContentElement
  #black listed content elements
  forbiddenContentElements:
    - ContentElement
```

## Custom Content Elements
Create a class which extends the ContentElement class or any other existing content element.

```php
<?php

class MyTextContentElement extends TextContentElement
{

    public static $singular_name = 'Text Content';

    public static $plural_name = 'Text Content';

    private static $db = array(
        'Splitview' => 'Boolean'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $field = new CheckboxField('MyCheckbox', 'a checkbox');
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }
}
```

