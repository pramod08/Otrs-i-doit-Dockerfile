<?php
/**
 * i-doit - Documentation and CMDB solution for IT environments
 *
 * This file is part of the i-doit framework. Modify at your own risk.
 *
 * Please visit http://www.i-doit.com/license for a full copyright and license information.
 *
 * @version     1.7.3
 * @package     i-doit
 * @author      synetics GmbH
 * @copyright   synetics GmbH
 * @url         http://www.i-doit.com
 * @license     http://www.i-doit.com/license
 */
namespace idoit\Module\Report\Validate;

use idoit\Module\Report\Protocol\Validation;

/**
 * Report
 *
 * @package     idoit\Module\Report
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.7.1
 */
class Query implements Validation
{
    /**
     * Method for validating that there are no updates, drops, truncates, ... inside the query.
     *
     * @param   string $p_query
     *
     * @return  boolean
     * @throws  \Exception
     */
    public static function validate($p_query)
    {
        if (empty($p_query))
        {
            return true;
        } // if

        // "\b" is used for "whole word only" - So that "isys_obj__updated" will not match.
        if (preg_match_all("/.*?(\bDROP|\bGRANT|\bINSERT|\bREPLACE|\bUPDATE|\bTRUNCATE|\bDELETE|\bALTER)[\s]*[a-zA-Z-_`]? .*?/is", $p_query, $l_register))
        {
            throw new \Exception(
                _L('LC__REPORT__POPUP__REPORT_PREVIEW__ERROR_MANIPULATION') . " " . _L(
                    'LC__SETTINGS__CMDB__VALIDATION__REGEX_CHECK_SUCCESS'
                ) . ": '" . $l_register[1][0] . "'."
            );
        } // if

        return true;
    } // function
}