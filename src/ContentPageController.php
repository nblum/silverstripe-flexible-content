<?php
declare (strict_types=1);

namespace Nblum\FlexibleContent;

use SilverStripe\Security\Permission;
use SilverStripe\Versioned\Versioned;

class ContentPageController extends \PageController
{

    private static $allowed_actions = array(
        'Elements',
        'FlexibleContent',
        'Render'
    );

    /**
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function FlexibleContent()
    {
        return $this->renderWith('FlexibleContent', $this->Elements());
    }

    /**
     * @return \SilverStripe\ORM\DataList
     */
    public function Elements()
    {
        $stage = Versioned::LIVE;

        if (isset($_GET['stage']) && Permission::check('CMS_ACCESS')) {
            $stage = $_GET['stage'];
        }

        $orderedIDs = explode(',', $this->getField('ElementsOrder'));

        return Versioned::get_by_stage(
            ContentElement::class,
            $stage,
            [
                'Active' => '1',
                'ParentID' => $this->getField('ID')
            ], [
                'field(ID,' . implode(',',$orderedIDs) . ') ASC'
            ]
        );
    }
}
