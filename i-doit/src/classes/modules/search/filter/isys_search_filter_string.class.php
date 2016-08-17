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
 * Search Filters
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.5.4, 1.6
 */
class isys_search_filter_string extends isys_search_filter implements isys_search_filter_interface
{

    /**
     * Get filter string
     *
     * @param int $p_wildcard
     *
     * @return string
     */
    public function get($p_wildcard = isys_search_filter::WILDCARD)
    {
        return $this->prepare($this->m_filter, $p_wildcard);
    }

    /**
     * Set filter string
     *
     * @param string $p_filter
     *
     * @return isys_search_filter_interface
     */
    public function set($p_filter)
    {
        $this->m_filter = strval($p_filter);

        return $this;
    }

    /**
     * @param string $p_filter
     */
    public function __construct($p_filter)
    {
        $this->set($p_filter);
    }
}