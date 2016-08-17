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
class isys_api_model_cmdb_object_type_categories extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isysgui_catg__id'                      => 'id',
        'isysgui_catg__title'                   => [
            '_L',
            'title'
        ],
        'isysgui_catg__const'                   => 'const',
        'isysgui_catg__parent'                  => 'parent',
        'isysgui_catg__list_multi_value'        => 'multi_value',
        'isysgui_catg__source_table'            => 'source_table',
        'isysgui_cats__id'                      => 'id',
        'isysgui_cats__title'                   => [
            '_L',
            'title'
        ],
        'isysgui_cats__const'                   => 'const',
        'isysgui_cats__parent'                  => 'parent',
        'isysgui_cats__list_multi_value'        => 'multi_value',
        'isysgui_cats__source_table'            => 'source_table',
        'isysgui_catg_custom__id'               => 'id',
        'isysgui_catg_custom__title'            => [
            '_L',
            'title'
        ],
        'isysgui_catg_custom__const'            => 'const',
        'isysgui_catg_custom__parent'           => 'parent',
        'isysgui_catg_custom__list_multi_value' => 'multi_value',
        'isysgui_catg_custom__source_table'     => 'source_table'
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => [
            'type' => [
                'type'        => 'int|string',
                'description' => 'Object type id or constant',
                'reference'   => 'isys_obj_type__id',
                'optional'    => true
            ]
        ]
    ];
    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [];

    /**
     * Fetches categories by object type.
     *
     * @param array $p_params Parameters:
     *                        int $p_params['type'] Object type
     *                        bool $p_params['raw'] (optional) Formatting. Can be any type of 0|1|'on'|'off'|true|false|'true'|'false'|... Defaults to false.
     *                        mixed $p_params['category'] (optional) Limit to one or more category types (int, string or array of ints/strings). Value(s) can be category identifiers, constants or short names ('global', 'specific' or 'custom').
     *
     * @return array Returns an empty array when an error occures.
     */
    public function read($p_params)
    {
        assert('is_array($p_params)');
        $l_categories = [];

        if (isset($p_params['id']))
        {
            $p_params['type'] = $p_params['id'];
        }

        if (isset($p_params['type']))
        {

            $l_dao = new isys_cmdb_dao_object_type($this->m_dao->get_database_component());

            $p_params['type'] = is_numeric($p_params['type']) ? $p_params['type'] : (defined($p_params['type']) ? constant($p_params['type']) : null);

            if ($p_params['type'] > 0 && $l_dao->get_objtype($p_params['type'])
                    ->num_rows() > 0
            )
            {
                // Raw mode:
                $l_raw = false;
                if (isset($p_params['raw']))
                {
                    $l_raw = filter_var($p_params['raw'], FILTER_VALIDATE_BOOLEAN);
                } // if

                // Global categories:
                $l_result = $l_dao->get_catg_by_obj_type(
                    $p_params['type']
                );

                while ($l_row = $l_result->get_row())
                {
                    if (class_exists($l_row['isysgui_catg__class_name']))
                    {
                        if ($l_raw)
                        {
                            $l_categories['catg'][] = $l_row;
                        }
                        else
                        {
                            $l_categories['catg'][] = $this->format_by_mapping($this->m_mapping, $l_row);
                        } // if
                    }
                } // while

                // Specific categories:
                $l_result = $l_dao->get_specific_category(
                    $p_params['type'],
                    C__RECORD_STATUS__NORMAL,
                    null,
                    true
                );
                while ($l_row = $l_result->get_row())
                {
                    if (class_exists($l_row['isysgui_cats__class_name']))
                    {
                        if ($l_raw)
                        {
                            $l_categories['cats'][] = $l_row;
                        }
                        else
                        {
                            $l_categories['cats'][] = $this->format_by_mapping($this->m_mapping, $l_row);
                        } // if
                    }
                } // while

                // Custom categories:
                $l_result = $l_dao->get_catg_custom_by_obj_type(
                    $p_params['type']
                );
                if (is_object($l_result))
                {
                    while ($l_row = $l_result->get_row())
                    {
                        if ($l_raw)
                        {
                            $l_categories['custom'][] = $l_row;
                        }
                        else
                        {
                            $l_categories['custom'][] = $this->format_by_mapping($this->m_mapping, $l_row);
                        } // if
                    } // while
                }
            }
            else
            {
                throw new isys_exception_api('Object type not found.');
            }
        }
        else
        {
            throw new isys_exception_api('Object type is missing.');
        }

        return $l_categories;
    } //function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function create($p_params)
    {
        throw new isys_exception_api('Creating is not possible here.');
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function delete($p_params)
    {
        throw new isys_exception_api('Deleting is not possible here.');
    } // function

    /**
     * Formats data array by mapping and encodes data to UTF-8.
     *
     * @param array $p_mapping The mapping itself
     * @param array $p_row     Data array
     *
     * @return array Formatted data array
     */
    protected function format_by_mapping(array $p_mapping, $p_row)
    {
        $l_return = [];

        foreach ($p_mapping as $l_key => $l_map)
        {
            if (isset($p_row[$l_key]) || is_array($l_map))
            {
                if (is_array($l_map))
                {
                    if (@$p_row[$l_key])
                    {
                        $l_return[$l_map[1]] = @call_user_func_array(
                            $l_map[0],
                            [
                                @$p_row[$l_key],
                                $p_row
                            ]
                        );
                    }
                }
                else
                {
                    $l_return[$l_map] = $p_row[$l_key];
                }
            }
        }

        return $l_return;
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function update($p_params)
    {
        throw new isys_exception_api('Updating is not possible here.');
    } // function

    /**
     * Constructor
     */
    public function __construct(isys_cmdb_dao &$p_dao)
    {
        $this->m_dao = $p_dao;
        parent::__construct();
    } // function

} // class

?>