<?php
/**
 * Created by PhpStorm.
 * User: nblum
 * Date: 22.02.18
 * Time: 06:51
 */

namespace Nblum\FlexibleContent;


use SilverStripe\ORM\FieldType\DBHTMLText;

interface IContentElement
{
    /**
     * template for rendering in front end
     * @return DBHTMLText
     */
    public function forTemplate(): DBHTMLText;

    /**
     * readable change date(time)
     * @return string
     */
    public function LastChange(): string;

    /**
     * backend preview of content
     * @return string
     */
    public function Preview(): string;

    /**
     * field type for backend
     * @return string
     */
    public function Type(): string;
}