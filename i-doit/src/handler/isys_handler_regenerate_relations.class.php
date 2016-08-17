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
 * Handler for regenerating relation objects
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_regenerate_relations extends isys_handler
{
    private $m_category_const = null;

    /**
     * Display the usage-info.
     */
    public function usage($p_error = false)
    {
        echo "\n\n" . C__COLOR__LIGHT_RED . "Renewing relation objects may take several minutes or hours to complete depending on the number of relation objects.\n" . C__COLOR__NO_COLOR;

        if ($p_error) echo "\n" . C__COLOR__LIGHT_RED . "Missing parameter!\n" . C__COLOR__NO_COLOR;

        echo "\nUsage:./regenerate_relations start\n" . "Parameter info:\n" . "start		- Start regenerating all relation objects.\n" . "start n		- Start regenerating relation objects for the specified category constant\n" . "Example:\n" . "./regenerate_relations start C__CATG__IP\n" . PHP_EOL;
        die;
    } // function

    public function init()
    {
        global $argv;

        verbose("Setting up system environment");

        try
        {
            $l_parameters = $argv;

            if (array_search('-h', $l_parameters) !== false)
            {
                $this->usage();
            } // if
            if (($l_start_index = array_search('start', $l_parameters)) === false)
            {
                $this->usage(true);
            } // if

            if (isset($l_parameters[$l_start_index + 1]))
            {
                $this->m_category_const = $l_parameters[$l_start_index + 1];
            } // if

            $this->regenerate_relations();
        }
        catch (Exception $e)
        {
            verbose($e->getMessage());
        } // try

        return true;
    } // function

    /**
     * This rebuilds the locations
     *
     * @throws Exception
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    private function regenerate_relations()
    {
        $l_dao = new isys_cmdb_dao_relation(isys_application::instance()->database);
        verbose('Rebuilding relation objects. This can take a while.', true);
        try
        {
            verbose('Removing unassigned relation objects.');
            $l_stats                 = $l_dao->delete_dead_relations();
            $l_dead_relation_objects = $l_stats[isys_cmdb_dao_relation::C__DEAD_RELATION_OBJECTS];

            if ($l_dead_relation_objects)
            {
                verbose('(' . $l_dead_relation_objects . ') unassigned relation objects deleted.');
            }
            else
            {
                verbose('No unassigned relation objects found');
            } // if

            $l_selected_category = null;

            if ($this->m_category_const !== null)
            {
                $l_sql = 'SELECT isysgui_catg__id AS id, ' . C__CMDB__CATEGORY__TYPE_GLOBAL . ' AS type FROM isysgui_catg ' . 'WHERE isysgui_catg__const = ' . $l_dao->convert_sql_text(
                        $this->m_category_const
                    ) . ' UNION ' . 'SELECT isysgui_cats__id AS id, ' . C__CMDB__CATEGORY__TYPE_SPECIFIC . ' AS type FROM isysgui_cats ' . 'WHERE isysgui_cats__const = ' . $l_dao->convert_sql_text(
                        $this->m_category_const
                    );

                $l_category_data     = $l_dao->retrieve($l_sql)
                    ->get_row();
                $l_selected_category = [
                    $l_category_data['type'] => [
                        $l_category_data['id'] => true
                    ]
                ];
            }
            $l_dao->regenerate_relations($l_selected_category);
            verbose("Relation objects were successfully renewed.");
        }
        catch (Exception $e)
        {
            verbose('Error with the following message: ' . $e->getMessage());
        } // try
    } // function
} // class
?>