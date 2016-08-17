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
 * Tenant handler
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */
class isys_handler_tenants extends isys_handler
{
    /**
     * Display the usage-info.
     */
    public function usage()
    {
        error(
            "Usage: ./tenant parameter [tenant-id]\n" . "Parameters: activate, deactivate, ls\n\n" . "ls         - list current tenants with status\n" . "activate   - activates an inactive tenant\n" . "deactivate - deactivates an active tenant\n"
        );
    } // function

    /**
     * @param   array $p_params
     *
     * @return  array
     */
    public function parse($p_params)
    {
        $l_ret = [];

        if (is_array($p_params))
        {
            foreach ($p_params as $l_value)
            {
                $l_tmp = explode("=", $l_value);

                $l_ret[$l_tmp[0]] = $l_tmp[1];
            } // foreach
        } // if

        return $l_ret;
    } // function

    /**
     * Display additional usage-info.
     */
    public function usage_add()
    {
        echo "Adding tenant:\n\n" . " ./tenant add option1=value [option2=value] [..]\n" . " Options:\n\n" . "  title=Title\n" . "  db_host=localhost\n" . "  db_port=3306\n" . "  db_user=root\n" . "  [db_pass=password]\n" . "  [lang_const=ISYS_LANG_GERMAN]\n" . "  [lang_short=de]\n" . "  [description=Description]\n" . "  [sort=10]\n" . "  [active=1]\n\n" . "The values in this example are the default values.\n";
    } // function

    /**
     * @param  array $p_set
     */
    public function add($p_set)
    {
        verbose("Adding tenant: " . $p_set["title"]);

        $l_dao = new isys_component_dao_mandator();

        if (empty($p_set["db_host"]) || empty($p_set["db_port"]) || empty($p_set["db_user"]) || empty($p_set["title"]))
        {
            $this->usage_add();
            error('');
        } // if

        if ($l_dao->add(
            $p_set["title"],
            $p_set["description"],
            $p_set["lang_const"],
            $p_set["lang_short"],
            $p_set["dir_cache"],
            $p_set["dir_tpl"],
            $p_set["db_host"],
            $p_set["db_port"],
            $p_set["db_user"],
            $p_set["db_pass"],
            $p_set["sort"],
            $p_set["active"]
        )
        )
        {
            verbose("Added");
        }
        else
        {
            verbose("Failed to add tenant - check database connection in config.inc.php");
        } // if
    } // function

    /**
     * @param $p_id
     */
    public function activate($p_id)
    {
        if (is_numeric($p_id) && $p_id > 0)
        {
            verbose("Activating tenant " . $p_id . "..");

            $l_dao = new isys_component_dao_mandator();

            if ($l_dao->activate_mandator($p_id))
            {
                verbose("Done.");
            }
            else
            {
                verbose("Failed.");
            }
            echo "\n";
        }
        else
        {
            verbose("Error. No ID given. Usage: tenant activate ID. Get IDs with tenant ls\n");
        }
    } // function

    /**
     * @param $p_id
     */
    public function deactivate($p_id)
    {
        if (is_numeric($p_id) && $p_id > 0)
        {
            verbose("Deactivating tenant " . $p_id . "..");

            $l_dao = new isys_component_dao_mandator();
            if ($l_dao->deactivate_mandator($p_id))
            {
                verbose("Done.");
            }
            else
            {
                verbose("Failed");
            }
            echo "\n";
        }
        else
        {
            verbose("Error. No ID given. Usage: tenant deactivate ID. Get IDs with ./tenant ls\n");
        }
    }

    /**
     * @return bool
     */
    public function ls()
    {
        $l_dao           = new isys_component_dao_mandator();
        $l_mandator_data = $l_dao->get_mandator(null, 0);

        echo "\n\nAvailable Tenants:\n";
        echo "ID: Title (Language) (host:port) [status]\n";
        while ($l_row = $l_mandator_data->get_row())
        {
            echo C__COLOR__LIGHT_PURPLE . $l_row["isys_mandator__id"] . C__COLOR__NO_COLOR . " : " . $l_row["isys_mandator__title"] . " (" . $l_row["isys_mandator__db_host"] . ":" . $l_row["isys_mandator__db_port"] . ")";

            if ($l_row["isys_mandator__active"] == 1) echo " [" . C__COLOR__LIGHT_GREEN . "active" . C__COLOR__NO_COLOR . "]";
            else echo " [" . C__COLOR__LIGHT_RED . "inactive" . C__COLOR__NO_COLOR . "]";

            echo "\n";
        }
        echo "\n";

        return true;
    }

    /**
     * @return bool|int
     */
    public function parse_params()
    {
        global $argv;

        $l_method = $argv[0];
        $l_id     = $argv[1];

        if (empty($l_method))
        {
            verbose("Wrong usage. I need at least one parameter");
            $this->usage();

            return false;
        }

        if (is_numeric($l_id))
        {
            $l_dao           = new isys_component_dao_mandator();
            $l_mandator_data = $l_dao->get_mandator($l_id, 0);

            if ($l_mandator_data->num_rows() > 0)
            {
                $l_row = $l_mandator_data->get_row();
                verbose(
                    "Using Tenant: " . $l_row["isys_mandator__title"] . " (" . $l_row["isys_mandator__db_host"] . ":" . $l_row["isys_mandator__db_port"] . ")"
                );
            }
            else
            {
                verbose("Tenant ID: " . $l_id . " does not exist.");
                $this->ls();

                return 0;
            }
        }

        if (method_exists($this, $l_method))
        {
            if ($l_id > 0)
            {
                return $this->$l_method($l_id);
            }
            else
            {
                return $this->$l_method($l_id);
            }
        }

        verbose("Method {$l_method} does not exist");

        return false;
    }

    /**
     * @return  mixed
     */
    public function login()
    {
        if (!empty($_SERVER['HTTP_HOST']))
        {
            die("Running this from a webbrowser is prohibited for security reasons!");
        } // if
    }

    /**
     * @return  mixed
     */
    public function init()
    {
        verbose("Tenant-Handler initialized (" . date("Y-m-d H:i:s") . ")");

        return $this->parse_params();
    } // function

    /**
     * Method for defining, if this handler needs the i-doit login.
     *
     * @return  boolean
     */
    public function needs_login()
    {
        return false;
    } // function
} // class