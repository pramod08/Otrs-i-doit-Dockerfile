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
class isys_api_model_cmdb_object_types extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_obj_type__id'                      => 'id',
        'isys_obj_type__title'                   => [
            '_L',
            'title'
        ],
        'isys_obj_type__container'               => 'container',
        'isys_obj_type__const'                   => 'const',
        'isys_obj_type__color'                   => 'color',
        'isys_obj_type__obj_img_name'            => [
            'isys_api_model_cmdb_object_types::get_image_url',
            'image'
        ],
        'isys_obj_type__icon'                    => 'icon',
        'isys_obj_type__isysgui_cats__id'        => 'cats',
        'isys_obj_type__isys_obj_type_group__id' => 'tree_group',
        'isys_obj_type__status'                  => 'status',
        'isys_obj_type_group__id'                => 'type_group',
        'isys_obj_type_group__title'             => [
            '_L',
            'type_group_title'
        ],
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => [
            'filter'   => [
                'type'        => 'array',
                'description' => 'Filter array',
                'optional'    => true
            ],
            'limit'    => [
                'type'        => 'int',
                'description' => 'Resultset limiting',
                'optional'    => true
            ],
            'sort'     => [
                'type'        => 'string',
                'description' => 'ASC or DESC',
                'optional'    => true
            ],
            'order_by' => [
                'type'        => 'string',
                'description' => 'Ordering by title, id or status',
                'optional'    => true
            ]
        ]
    ];
    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [

    ];

    /**
     * Return URL to regtrieve the object image
     *
     * @param $p_filename
     * @param $p_row
     *
     * @return string
     */
    public static function get_image_url($p_filename, $p_row)
    {
        return isys_helper_link::get_base() . "images/objecttypes/" . @$p_row['isys_obj_type__obj_img_name'];
    }

    /**
     * Fetches object types by filter.
     *
     * @param array $p_params Parameters:
     *
     *   int $p_params['filter']['id'] (optional) Object type identifier or Constants
     *   array $p_params['filter']['ids'] (optional) Object type identifiers or Constants
     *   string $p_params['filter']['title'] (optional) Object type title
     *   array $p_params['filter']['titles'] (optional) Object type titles
     *   bool $p_params['filter']['enabled'] (optional) Show only object types
     *   enabled or disabled in GUI. Can be any type of
     *   0|1|'on'|'off'|true|false|'true'|'false'|... Defaults to booth.
     *
     *   bool $p_params['raw'] (optional) Formatting. Can be any type of
     *   0|1|'on'|'off'|true|false|'true'|'false'|... Defaults to false.
     *
     *   string $p_params['order_by'] (optional) Order by one of the supported
     *   filter arguments. Defaults to null that means result will be ordered by
     *   object identifiers.
     *
     *   string $p_params['sort'] (optional) Order result ascending ('ASC') or
     *   descending ('DESC').
     *
     *   int $p_params['limit'] (optional) Limitation: where to start and number
     *   of elements, i.e. 0 or 0,10. Defaults to null that means no limitation.
     *
     * @return array Objects types. Returns an empty array on error.
     *
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function read($p_params)
    {
        assert('is_array($p_params)');

        if (array_key_exists('filter', $p_params))
        {
            assert('is_array($p_params["filter"])');
        }
        else
        {
            $p_params['filter'] = [];
        }

        // Force limit to record status 'normal':
        $p_params['filter']['status'] = C__RECORD_STATUS__NORMAL;

        // Raw mode:
        $l_raw = false;
        if (array_key_exists('raw', $p_params))
        {
            $l_raw = filter_var($p_params['raw'], FILTER_VALIDATE_BOOLEAN);
        } // if

        // Order by:
        $l_order_by = null;
        if (array_key_exists('order_by', $p_params))
        {
            $l_order_by = $p_params['order_by'];
        } // if

        // Sort:
        $l_sort = null;
        if (array_key_exists('sort', $p_params))
        {
            $l_sort = $p_params['sort'];
        } // if

        // Limitation:
        $l_limit = null;
        if (array_key_exists('limit', $p_params))
        {
            $l_limit = $p_params['limit'];
        } // if

        // Show object count
        $l_count = false;
        if (isset($p_params['countobjects']))
        {
            if ($p_params['countobjects'])
            {
                $l_count = true;
            }
            else $l_count = false;
        }

        // Data retrieval:
        $l_data = $this->m_dao->get_object_types_by_properties(
            $p_params['filter'],
            $l_order_by,
            $l_sort,
            $l_limit,
            $l_count
        );

        $l_return = [];

        // Data formatting:
        while ($l_row = $l_data->get_row())
        {
            if ($l_row['isys_obj_type__id'] != C__OBJTYPE__LOCATION_GENERIC && $l_row['isys_obj_type__id'] != C__OBJTYPE__MIGRATION_OBJECT)
            {
                $l_new = [];
                if ($l_raw)
                {
                    $l_new = $l_row;
                }
                else
                {
                    $l_new = $this->format_by_mapping($this->m_mapping, $l_row);

                    if ($l_count)
                    {
                        $l_new['objectcount'] = $this->m_dao->count_objects(null, $l_row['isys_obj_type__id'], true);
                    }
                } // if raw

                $l_return[] = $l_new;
            }
        } // while

        // Order by translated titles:
        if ($l_order_by === 'title' && $l_raw === false)
        {
            usort(
                $l_return,
                [
                    $this,
                    'sort_by_title'
                ]
            );
        } //if

        return $l_return;
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