# silverstripe-flexible-content
Content pages based on data objects for better content structure

![screenshot](assets/screen1.jpg)


## Features
 - Supports draft/publish state
 - Drag n drop reordering
 - Black/white listing of block types
 
### Disregards
 - Same content element on different pages


## Installation

```sh
composer require nblum/silverstripe-flexible-content
```

For a basic set of content element you may install the 
[elements-package](https://github.com/nblum/silverstripe-flexible-content-elements):
 
```sh
composer require nblum/silverstripe-flexible-content-elements
```

 1. Create a page layout template (`templates/Layout`) ```FlexibleContentPage``` in your theme and add
    ```
        $FlexibleContent
    ```
 1. Run ```dev/build?flush=1```
 1. Change the Page type to "Flexible Content Page" of every page you like


## Configuration

Edit your config.yml file and add the following lines. This is the default config and
only necessary if changes are neede
```yml
FlexibleContent:
  availableContentElements:
    false
  forbiddenContentElements:
    - ContentElement
```

## Custom Content Elements
Create a class which extends the ContentElement class or any other existing content element.

```php
<?php
declare (strict_types=1);

use Nblum\FlexibleContent\ContentElements\TextContentElement;
use Nblum\FlexibleContent\IContentElement;

class MyTextContentElement extends TextContentElement implements IContentElement
{

    private static $singular_name = 'My Text Content Element';

    private static $db = array(
        'Splitview' => 'Boolean'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $field = new \SilverStripe\Forms\CheckboxField('MyCheckbox', 'a checkbox');
        $fields->addFieldToTab('Root.Main', $field);

        return $fields;
    }
}
```

And provide a include template file (eg `themes/mytheme/templates/Includes/MyTextContentElement.ss`) with the same name

```html
    <div <%if $Splitview %> class="splitview" <% end_if %>
        $Content
    </div>
```

For more examples have a look at the
[elements-package](https://github.com/nblum/silverstripe-flexible-content-elements)
 