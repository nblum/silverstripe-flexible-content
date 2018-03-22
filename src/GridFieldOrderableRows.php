<?php

namespace Nblum\FlexibleContent;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\Connect\DatabaseException;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;

/**
 * overwrites the class to save the order in the holder page instead of every data object
 * this is only a fast-working solution and should be cleaned up to work without extending
 *
 * Class GridFieldOrderableRows
 * @package Nblum\FlexibleContent
 */
class GridFieldOrderableRows extends \Symbiote\GridFieldExtensions\GridFieldOrderableRows
{
    private static $allowed_actions = array(
        'handleReorder'
    );

    public function getURLHandlers($grid)
    {
        return array(
            'POST reorder' => 'handleReorder',
            'POST movetopage' => 'handleMoveToPage'
        );
    }


    public function getManipulatedData(GridField $grid, SS_List $list)
    {
        return $list;
    }

    public static function orderedContentElements(DataObject $page)
    {
        $orderedIDs = explode(',', $page->getField('ElementsOrder'));

        try {
            $dataList = ContentElement::get()
                ->filter('ParentID', $page->getField('ID'));

            foreach ($dataList as $item) {
                if (!in_array($item->getField('ID'), $orderedIDs)) {
                    $orderedIDs[] = $item->getField('ID');
                }
            }

            $dataList = ContentElement::get()
                ->filter('ParentID', $page->getField('ID'))
                ->where('ID IN(' . implode(',', $orderedIDs) . ')')
                ->sort('field(ID,' . implode(',', $orderedIDs) . ') ASC');
        } catch (\Exception $e) {
            return ContentElement::get()
                ->filter('ParentID', $page->getField('ID'));
        }

        return $dataList;
    }

    /**
     * @param GridField $grid
     * @param \Symbiote\GridFieldExtensions\SS_HTTPRequest $request
     * @return string|\Symbiote\GridFieldExtensions\SS_HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function handleReorder($grid, $request)
    {
        //get current page
        $pageID = $request->param('ID');
        $page = DataObject::get_by_id(\Page::class, $pageID);

        //get new order
        $data = $request->postVar($grid->getName());
        $orderedIDs = $this->getSortedIDs($data);

        //save it
        $page->setField('ElementsOrder', implode(',', $orderedIDs));
        $page->write();

        //update output for response
        $grid->setList(self::orderedContentElements($page));
        return $grid->FieldHolder();
    }

}
