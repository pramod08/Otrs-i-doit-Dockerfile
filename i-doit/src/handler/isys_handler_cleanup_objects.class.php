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
 * Handler: Cleanup objects
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_cleanup_objects extends isys_handler
{
    public function init()
    {
        global $argv;

        if (array_search('-t', $argv) === false)
        {
            error(
                C__COLOR__LIGHT_RED . "Missing Parameter: \n" . C__COLOR__NO_COLOR . "You have to use parameter -t to start cleaning up the specified status:\n" . "-t " . C__RECORD_STATUS__BIRTH . " for 'unfinished' objects, " . C__RECORD_STATUS__ARCHIVED . " for 'archived' objects and " . C__RECORD_STATUS__DELETED . " for 'deleted' objects." . "\nExample for cleaning up deleted objects: ./controller -v -m cleanup_objects -t " . C__RECORD_STATUS__DELETED . "\n"
            );
        } // if

        $l_slice = array_search('-t', $argv) + 1;
        $l_cmd   = array_slice($argv, $l_slice);
        $l_type  = trim($l_cmd[0]);

        verbose("Setting up system environment");

        try
        {
            $this->cleanup($l_type);
        }
        catch (Exception $e)
        {
            verbose($e->getMessage());
        } // try

        return true;
    } // function

    private function cleanup($p_type)
    {
        global $g_comp_database;

        verbose("Starting cleanup... ");

        try
        {
            $l_module_system = isys_factory::get_instance('isys_module_system', $g_comp_database);
            $l_count         = $l_module_system->cleanup_objects($p_type);

            verbose(sprintf(_L("LC__SYSTEM__REMOVE_BIRTH_OBJECTS_DONE"), $l_count));
        }
        catch (Exception $e)
        {
            throw new Exception($e);
        } // try

        verbose("Done", false, false);
    } // function
} // class
?>