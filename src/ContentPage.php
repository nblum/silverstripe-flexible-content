<?php
declare (strict_types=1);

namespace Nblum\FlexibleContent;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\SS_List;
use SilverStripe\Versioned\VersionedGridFieldItemRequest;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;

/**
 * Class ContentPage
 * @package Nblum\FlexibleContent
 */
class ContentPage extends \Page
{

    const CONFIG_KEY = 'FlexibleContent';

    private static $db = array(
        'ElementsOrder' => 'Varchar(255)'
    );

    private static $has_many = [
        'ContentElements' => ContentElement::class
    ];

    /**
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        //disable ContentPage (but not FlexibleContentPage) from cms selection
        if (get_class($this) === ContentPage::class) {
            return false;
        }
        return parent::canCreate($member, $context);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab('Root.Main', 'Content');
        $fields->addFieldToTab('Root.FlexibleContent', $this->getGridField());
        return $fields;
    }

    public function publishRecursive()
    {
        parent::publishRecursive();

        $children = $this->ContentElements();
        /* @var ContentElement[] $children */
        foreach ($children as $child) {
            $child->publishRecursive();
        }
    }


    protected function getGridField(): GridField
    {
        $gridField = new GridField(
            'ContentElement',
            'Content Elements',
            GridFieldOrderableRows::orderedContentElements($this)
        );
        $dataColumns = new GridFieldDataColumns();


        $dataColumns->setDisplayFields([
            'Type' => 'Type',
            'Name' => 'Name',
            'Preview' => 'Preview',
            'getSingularName' => 'Type',
            'LastChange' => 'Changed',
            'PublishState' => 'PublishState'
        ]);

        $config = GridFieldConfig::create();
        $config->addComponent($dataColumns);
        $config->addComponent(new GridFieldEditButton());
        $config->addComponent(new GridFieldDeleteAction());
        $config->addComponent(new GridFieldOrderableRows());
        $config->addComponent(new GridFieldFilterHeader(), GridFieldDataColumns::class);

        //fixes not working add/edit buttons
        $config
            ->addComponent(new GridFieldDetailForm())
            ->getComponentByType(GridFieldDetailForm::class)
            ->setItemRequestClass(VersionedGridFieldItemRequest::class);

        $availableContentElements = $this->getAvailableClasses();
        //@todo create a AddContentElementButton for a single element
//        if (count($availableContentElements) === 1) {
//            $contentElementClassName = array_shift($availableContentElements);
//            $addNewButton = new GridFieldAddNewButton();
//            $clsInstance = new $contentElementClassName;
//            $name = $clsInstance->singular_name();
//            $addNewButton->setButtonName($name);
//            $config->addComponent($addNewButton);
//        } elseif (count($availableContentElements) > 1) {
        $mc = new GridFieldAddNewMultiClass();
        $mc->setClasses($availableContentElements);
        $config
            ->removeComponentsByType(GridFieldAddNewButton::class)
            ->addComponent($mc);
//        }

        $gridField->setConfig($config);
        return $gridField;
    }

    /**
     * @return array|scalar
     */
    protected function getAvailableClasses(): array
    {
        $allowedClasses = Config::inst()->get(self::CONFIG_KEY, 'availableContentElements');
        if (is_array($allowedClasses)) {
            foreach ($allowedClasses as $key => $class) {
                if (!ClassInfo::exists($class)) {
                    unset($allowedClasses[$key]);
                }
            }
            return $allowedClasses;
        }
        $forbiddenClasses = Config::inst()->get(self::CONFIG_KEY, 'forbiddenContentElements');
        $classes = array_values(ClassInfo::implementorsOf(IContentElement::class));
        if (is_array($forbiddenClasses)) {
            foreach ($forbiddenClasses as $class) {
                $key = array_search($class, $classes);
                if ($key !== false) {
                    unset($classes[$key]);
                }
            }
        }
        return is_array($classes) ? $classes : [];
    }
}
