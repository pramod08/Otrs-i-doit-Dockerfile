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
class isys_api_model_cmdb_reports extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_report__id'           => 'id',
        'category_title'            => 'category',
        'isys_report__title'        => 'title',
        'isys_report__description'  => 'description',
        'isys_report__datetime'     => 'created',
        'isys_report__last_editied' => 'modified',
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read'   => [
            'id' => [
                'type'        => 'int',
                'description' => 'Report ID',
                'reference'   => 'isys_report__id',
                'optional'    => true
            ]
        ],
        'create' => [],
        'update' => [],
        'delete' => [],
    ];
    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [];

    /**
     * Fetches information about an object.
     *
     * @param array $p_params Parameters. Structure:
     *                        array(
     *                        'id' => 1
     *                        )
     *
     * @return array Returns an empty array when an error occures.
     */
    public function read($p_params)
    {
        global $g_comp_database_system;

        $l_return = [];

        $l_dao = new isys_report_dao($g_comp_database_system);
        if (isset($p_params['id']))
        {
            $this->m_log->info('Retrieving report with id ' . $p_params['id']);

            $l_row = $l_dao->get_report($p_params['id']);

            if ($l_row && $l_row['isys_report__query'])
            {
                // Execute report
                $l_report_res = $l_dao->query($l_row['isys_report__query']);

                /* Remove __id__ keys as of __obj_id__ is available as well */
                array_walk(
                    $l_report_res['content'],
                    [
                        $this,
                        'remove_id'
                    ]
                );

                return $l_report_res['content'];
            }
            else throw new isys_exception_api(sprintf('Report with id %s does not exist', $p_params['id']));

        }
        else
        {
            $l_reports = $l_dao->get_reports();
            while ($l_row = $l_reports->get_row())
            {
                $l_return[] = $this->format_by_mapping($this->m_mapping, $l_row);
            }
        }

        return $l_return;
    } // function

    /**
     * @param $p_array
     */
    public function remove_id(&$p_array)
    {
        unset($p_array['__id__']);
    }

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
        parent::__construct();
    } // function

} // class

?>