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
 * API model for CMDB
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @author     Benjamin Heisig <bheisig@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_objects extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data mapping used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_obj__id'                     => 'id',
        'isys_obj__title'                  => 'title',
        'isys_obj__sysid'                  => 'sysid',
        'isys_obj__isys_obj_type__id'      => 'type',
        'isys_obj__created'                => 'created',
        'isys_obj__updated'                => 'updated',
        'isys_obj_type__title'             => [
            '_L',
            'type_title'
        ],
        'isys_obj_type__icon'              => 'type_icon',
        'isys_obj_type_group__id'          => 'type_group',
        'isys_obj_type_group__title'       => [
            '_L',
            'type_group_title'
        ],
        'isys_obj__status'                 => 'status',
        'isys_obj__isys_cmdb_status__id'   => 'cmdb_status',
        'isys_cmdb_status__title'          => [
            '_L',
            'cmdb_status_title'
        ],
        'isys_catg_image_list__image_link' => [
            'isys_api_model_cmdb_objects::get_image_url',
            'image'
        ]
    ];
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
     * Return URL to regtrieve the object image
     *
     * @param $p_filename
     * @param $p_row
     *
     * @return string
     */
    public static function get_image_url($p_filename, $p_row)
    {
        return isys_helper_link::get_base() . isys_smarty_plugin_object_image::get_user_defined_image_url_by_file($p_filename, @$p_row['isys_obj_type__id']);
    }

    /**
     * Fetches objects by filter.
     *
     * @param array $p_params Parameters:
     *                        array $p_params['filter']['ids'] (optional) Object identifiers
     *                        int $p_params['filter']['type'] (optional) Object type
     *                        int $p_params['filter']['type_group'] (optional) Object type group
     *                        int | string $p_params['filter']['status'] (optional) Record-Status
     *                        int $p_params['filter']['title'] (optional) Object title
     *                        int $p_params['filter']['sysid'] (optional) SYSID
     *                        int $p_params['filter']['first_name'] (optional) First name (person)
     *                        int $p_params['filter']['last_name'] (optional) Last name (person)
     *                        int $p_params['filter']['email'] (optional) Email address (person)
     *                        int $p_params['filter']['location'] (optional) Location tree
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
     * @return array Objects. Returns an empty array on error.
     *
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function read($p_params)
    {
        // Force limit to record status 'normal':
        if (isset($p_params['filter']['status']))
        {
            if (!is_numeric($p_params['filter']['status']) && is_string($p_params['filter']['status']) && defined($p_params['filter']['status']))
            {
                $p_params['filter']['status'] = constant($p_params['filter']['status']);
            }
            else
            {
                $p_params['filter']['status'] = C__RECORD_STATUS__NORMAL;
            }
        }
        else
        {
            $p_params['filter']['status'] = C__RECORD_STATUS__NORMAL;
        }

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

        // Data retrieval:
        $l_data = $this->m_dao->get_objects(
            $p_params['filter'],
            $l_order_by,
            $l_sort,
            $l_limit
        );

        $l_return = [];

        // Data formatting:
        while ($l_row = $l_data->get_row())
        {

            $l_new = null;
            if ($l_raw)
            {
                $l_new = $l_row;
            }
            else
            {
                $l_new = $this->format_by_mapping($this->m_mapping, $l_row);
            } // if raw
            $l_return[] = $l_new;
        } // while

        // Sort by translated titles:
        if ($l_sort === 'title' && $l_raw === false)
        {
            usort(
                $l_return,
                [
                    $this,
                    'sort_by_title'
                ]
            );
        } //if

        // Add location:
        if (isset($p_params['filter']) && array_key_exists('location', $p_params['filter']))
        {
            $l_location_dao  = new isys_cmdb_dao_category_g_location($this->m_db);
            $l_location_tree = $l_location_dao->get_location_tree()
                ->__as_array();
            $l_id            = $l_raw ? 'isys_obj__id' : $this->m_mapping['isys_obj__id'];

            $l_format_location = function ($l_entity_id) use (&$l_format_location, $l_location_tree)
            {
                foreach ($l_location_tree as $l_node)
                {
                    if ($l_node['id'] == $l_entity_id)
                    {
                        return array_merge(
                            $l_format_location($l_node['parentid']),
                            [$l_node['title']]
                        );
                    } //if
                } //foreach

                return [];
            }; //function

            foreach ($l_return as $l_index => $l_entity)
            {
                $l_return[$l_index]['location'] = array_slice(
                    $l_format_location($l_entity[$l_id]),
                    0,
                    -1
                );
            } //foreach
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
     * Deletes an object.
     *
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function delete($p_params)
    {
        $l_return = [
            'message' => 'Error while deleting object(s)',
            'success' => false
        ];

        if (isset($p_params['ids']))
        {
            $p_params['id'] = $p_params['ids'];
        }

        if (!isset($p_params['id']))
        {
            throw new isys_exception_api('Object id(s) missing');
        }
        else
        {

            if (is_numeric($p_params['id']))
            {
                $p_params['id'] = [$p_params['id']];
            }

            if (is_array($p_params['id']))
            {
                foreach ($p_params['id'] as $l_id)
                {
                    if (!$this->m_dao->set_object_status($l_id, C__RECORD_STATUS__DELETED))
                    {
                        throw new isys_exception_api(sprintf('Error while deleting object with id %s', $l_id));
                    }
                }
                $l_return['message'] = 'Object(s) successfully deleted';
                $l_return['success'] = true;
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