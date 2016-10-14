<?php


class ContentPage extends \Page implements \PermissionProvider
{
    const CONFIG = 'FlexibleContent';

    public static $has_many = array(
        'ContentElements' => 'ContentElement'
    );

    public function providePermissions()
    {
        return array(
            'FLEXIBLE_CONTENT_SORT' => array(
                'category' => _t('FlexibleContent.permission.category'),
                'name' => _t('ContentPage.permission.sort.name'),
            )
        );
    }

    public function publish($fromStage, $toStage, $createNewVersion = false)
    {
        parent::publish($fromStage, $toStage, $createNewVersion);

        //publish all children
        $contentElements = $this->ContentElements();
        /* @var ContentElement $contentElement */
        foreach ($contentElements as $contentElement) {
            $contentElement->doPublish();
        }
    }

    public function doUnpublish()
    {
        $result = parent::doUnpublish();

        if (!$result) {
            return false;
        }

        //publish all children
        $contentElements = $this->ContentElements();
        /* @var ContentElement $contentElement */
        foreach ($contentElements as $contentElement) {
            \Heyday\VersionedDataObjects\VersionedReadingMode::setLiveReadingMode();
            $clone = clone $contentElement;
            $clone->delete();
            \Heyday\VersionedDataObjects\VersionedReadingMode::restoreOriginalReadingMode();
        }

        return $result;
    }

    public function delete()
    {
        parent::delete();

        //publish all children
        $contentElements = $this->ContentElements();
        /* @var ContentElement $contentElement */
        foreach ($contentElements as $contentElement) {
            $contentElement->delete();
        }
    }

    public function getCMSFields()
    {
        \Requirements::javascript(FLEXIBLE_CONTENT_PLUGIN_PATH . '/javascript/admin.js');
        \Requirements::css(FLEXIBLE_CONTENT_PLUGIN_PATH . '/css/admin.css');

        $fields = parent::getCMSFields();

        $fields->removeFieldFromTab('Root.Main', 'Content');
        $fields->removeFieldFromTab('Root.Main', 'Metadata');


        $fields->addFieldToTab('Root.Main', new \HeaderField(_t('ContentPage.header')));

        $elementSelector = new \GridFieldAddNewMultiClass();
        $elementSelector->setClasses($this->getAvailableClasses());

        $config = \GridFieldConfig::create();
        $config->addComponent(new \GridFieldButtonRow('before'));
        if (\Permission::checkMember(\Member::currentUser(), 'FLEXIBLE_CONTENT_EDIT')) {
            $config->addComponent(new \GridFieldEditButton());
        }
        $config->addComponent(new \Heyday\VersionedDataObjects\VersionedDataObjectDetailsForm());
        $config->addComponent($columns = new \GridFieldDataColumns());
        $config->addComponent($elementSelector);
        if (\Permission::checkMember(\Member::currentUser(), 'FLEXIBLE_CONTENT_SORT')) {
            $config->addComponent(new \GridFieldOrderableRows());
        }
        $config->addComponent(new \GridFieldDeleteAction());
        $config->addComponent(new \Nblum\FlexibleContent\GridFieldActiveAction());
        if (class_exists('GridFieldCopyToSessionButton')) {
            $config->addComponent(new \Nblum\FlexibleContent\GridFieldCopyToSessionButton());
            $config->addComponent(new \Nblum\FlexibleContent\GridFieldPasteSessionButton());
        }

        $columns->setDisplayFields([
            'Name' => 'Name',
            'Preview' => 'Preview',
            'getSingularName' => 'Type',
            'LastChange' => 'Changed',
            'PublishState' => 'PublishState'
        ]);

        //convert Publish state to raw html output
        $dataColumns = $config->getComponentByType('GridFieldDataColumns');
        $dataColumns->setFieldCasting([
            'Preview' => 'HTMLText->RAW',
            'PublishState' => 'HTMLText->RAW'
        ]);

        $columns->setFieldFormatting([
            'ClassName' => function($name) {
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
        $grid = new \GridField(
            'ContentElement',
            'ContentElements',
            $this->ContentElements(),
            $config
        );

        $grid->addExtraClass('flexible-content-grid');

        $fields->addFieldToTab('Root.Main', $grid);
        return $fields;
    }

    /**
     * @return array|scalar
     */
    protected function getAvailableClasses()
    {
        $allowedClasses = \Config::inst()->get(self::CONFIG, 'availableContentElements');
        if (is_array($allowedClasses)) {
            foreach ($allowedClasses as $key => $class) {
                if (!\ClassInfo::exists($class)) {
                    unset($allowedClasses[$key]);
                }
            }

            return $allowedClasses;
        }

        $forbiddenClasses = \Config::inst()->get(self::CONFIG, 'forbiddenContentElements');
        $classes = array_values(\ClassInfo::subclassesFor('ContentElement'));

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


class ContentPage_Controller extends \Page_Controller
{

    private static $allowed_actions = array(
        'Elements',
        'FlexibleContent',
        'Render'
    );

    /**
     * renders content with flexibleContent template
     * @return HTMLText
     */
    public function FlexibleContent()
    {
        return $this->renderWith('FlexibleContent', $this->Elements());
    }

    /**
     * creates List of all rows with content
     * @return DataList
     */
    public function Elements()
    {
        $defaultStage = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::getDefaultStage();
        if (isset($_GET['stage']) && $defaultStage == $_GET['stage'] && \Permission::check('CMS_ACCESS')) {
            $stage = $defaultStage;
        } else {
            $stage = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::get_live_stage();
        }

        $results = \Nblum\FlexibleContent\FlexibleContentVersionedDataObject::get_by_stage(
            \ContentElement::class,
            $stage
            , [
            'Active' => '1'
        ], [
            'Sort' => 'ASC'
        ]);

        return $results;
    }
}