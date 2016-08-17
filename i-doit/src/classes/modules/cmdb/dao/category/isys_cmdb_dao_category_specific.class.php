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

/**
 * i-doit
 *
 * DAO: abstraction layer for CMDB Specific Categories.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_cmdb_dao_category_specific extends isys_cmdb_dao_category
{
    /**
     * Category type's identifier.
     *
     * @var  integer
     */
    protected $m_cat_type = C__CMDB__CATEGORY__TYPE_SPECIFIC;
    /**
     * Category type's abbrevation.
     *
     * @var  string
     */
    protected $m_category_type_abbr = 'cats';
    /**
     * Category type's constant.
     *
     * @var  string
     */
    protected $m_category_type_const = 'C__CMDB__CATEGORY__TYPE_SPECIFIC';
} // class