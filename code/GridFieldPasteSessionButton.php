<?php

namespace Nblum\FlexibleContent;

/**
 * This component provides a button for opening the add new form provided by
 * {@link GridFieldDetailForm}.
 *
 * Only returns a button if {@link DataObject->canCreate()} for this record
 * returns true.
 *
 * @package forms
 * @subpackage fields-gridfield
 */
class GridFieldPasteSessionButton implements
    \GridField_HTMLProvider,
    \GridField_URLHandler  {

    private static $allowed_actions = array(
        'pasteSession'
    );

    protected $title;
    protected $fragment;
    protected $searchList;

    /**
     * @param string $fragment
     */
    public function __construct($fragment = 'buttons-before-left') {
        $this->fragment = $fragment;
        $this->title    = _t('GridFieldExtensions.PASTESESSION', 'Paste');
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     * @return GridFieldPasteSessionButton $this
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getFragment() {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     * @return GridFieldPasteSessionButton $this
     */
    public function setFragment($fragment) {
        $this->fragment = $fragment;
        return $this;
    }

    /**
     * @return bool
     */
    protected function isCopyAvailable()
    {
        return isset($_SESSION['flexible-content']['copy']);
    }

    /**
     * @return mixed
     */
    protected function getCopy()
    {
        return json_decode($_SESSION['flexible-content']['copy'], true);
    }


    public function getHTMLFragments($grid) {
        \GridFieldExtensions::include_requirements();

        $data = new \ArrayData(array(
            'Title' => $this->getTitle(),
            'Link'  => $grid->Link('paste-session')
        ));

        $copy = $this->getCopy();


        //for ajax requests (copy, ...)
        /* @var SS_HTTPRequest $request */
        $request = $grid->getForm()->getController()->getRequest();
        //for initial load
        $urlParams = $grid->getForm()->getController()->getUrlParams();
        $pageId = (int) $request->postVar('ID') !== 0 ? (int) $request->postVar('ID') : (int) $urlParams['ID'];

        return array(
            $this->fragment => $data->renderWith('GridFieldPasteSessionButton', [
                'isCopyAvailable' => $this->isCopyAvailable(),
                'copyTitle' => $this->isCopyAvailable() ? $copy['title'] : '',
                'pageId' => $pageId
            ]),
        );
    }

    public function getURLHandlers($grid) {
        return array(
            'paste-session' => 'pasteSession'
        );
    }

    public function pasteSession(\GridField $grid, \SS_HTTPRequest $request) {
        $copy = $this->getCopy();

        $item = \DataObject::get_by_id(\ContentElement::class, (int) $copy['id']);

        $pageId = (int) $request->postVar('pageId');
        if ($pageId === 0) {
            throw new \RuntimeException(
                _t('GridFieldAction_Copy.PageNotFound', 'Could not determine current page'), 0);
        }

        if (!($item instanceof \ContentElement)) {
            //unset($_SESSION['flexible-content']['copy']);
            throw new \RuntimeException(
                _t('GridFieldAction_Copy.ItemNotFound', 'No item to copy found'), 0);
        }

        if (!$item->canCreate()) {
            throw new \ValidationException(
                _t('GridFieldAction_Copy.CreatePermissionsFailure', "No create permissions"), 0);
        }

        /* @var ContentElement $clone */
        $clone = $item->duplicate(false);
        $clone->setField('ID', null);
        $clone->setField('ParentID', $pageId);
        $clone->setField('Name', $clone->getField('Name') . '(copy)');
        $clone->write();
        if (!$clone || $clone->ID < 1) {
            user_error("Error Duplicating!", E_USER_ERROR);
            return;
        }

        unset($_SESSION['flexible-content']['copy']);

    }

}
