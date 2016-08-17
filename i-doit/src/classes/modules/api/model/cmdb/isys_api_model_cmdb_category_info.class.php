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
 * API model
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_category_info extends isys_api_model_cmdb_category implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => []
    ];
    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [];

    /**
     * @param array $p_params Parameters (depends on data method)
     *
     * @internal param string $p_method Data method
     * @return isys_api_model_cmdb Returns itself.
     */
    public function create($p_params)
    {
        return null;
    } // function

    /**
     * @param array $p_params Parameters (depends on data method)
     *
     * @internal param string $p_method Data method
     * @return isys_api_model_cmdb Returns itself.
     */
    public function delete($p_params)
    {
        return null;
    } // function

    /**
     * Get category info
     *
     * @param array $p_params
     *
     * @throws isys_exception_api
     * @return array
     */
    public function read($p_params)
    {
        /* Init */
        $l_return = [];

        if (($l_cat = $this->prepare($p_params)))
        {

            if (method_exists($l_cat, 'get_properties'))
            {

                $l_properties     = [];
                $l_properties_tmp = $l_cat->get_properties();

                foreach ($l_properties_tmp AS $l_propkey => $l_propdata)
                {

                    $l_properties[$l_propkey] = [
                        'title'             => _L(@$l_propdata[C__PROPERTY__INFO]['title']),
                        C__PROPERTY__INFO   => $l_propdata[C__PROPERTY__INFO],
                        C__PROPERTY__DATA   => $l_propdata[C__PROPERTY__DATA],
                        C__PROPERTY__UI     => $l_propdata[C__PROPERTY__UI],
                        C__PROPERTY__FORMAT => $l_propdata[C__PROPERTY__FORMAT],
                        C__PROPERTY__CHECK  => [
                            C__PROPERTY__CHECK__MANDATORY => $l_propdata[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]
                        ]
                    ];

                } // foreach

                return $l_properties;
            }
            else
            {
                throw new isys_exception_api('get_properties method does not exist for ' . get_class($l_cat), -32601);
            } // if

        } // if

        return $l_return;
    } // function

    /**
     * @param array $p_params Parameters (depends on data method)
     *
     * @internal param string $p_method Data method
     * @return isys_api_model_cmdb Returns itself.
     */
    public function update($p_params)
    {
        return null;
    } // function
} // class

?>