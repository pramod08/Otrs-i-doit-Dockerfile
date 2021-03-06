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
 * DAO: Category list for contacts.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      André Wösten <awoesten@i-doit.org>
 * @version     Dennis Blümer
 * @version     Van Quyen Hoang
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_contact extends isys_cmdb_dao_list
{
    /**
     * Counter for the dialog smarty-plugin.
     *
     * @var  integer
     */
    protected $m_i = 0;

    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__CONTACT;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Method for retrieving the category-data.
     *
     * @param   mixed   $p_unused
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_unused, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_sql = "SELECT *, isys_catg_mail_addresses_list__title AS mail_address
			FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			LEFT JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_cats_person_group_list ON isys_cats_person_group_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_cats_organization_list ON isys_cats_organization_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_connection__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE TRUE ";

        if (!empty($p_objID))
        {
            $l_sql .= "AND isys_catg_contact_list__isys_obj__id = " . $this->convert_sql_id($p_objID);
        } // if

        if (!empty($l_cRecStatus))
        {
            $l_sql .= " AND isys_catg_contact_list__status = " . $this->convert_sql_id($l_cRecStatus);
        } // if

        return $this->retrieve($l_sql . " AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " GROUP BY isys_catg_contact_list__id;");
    } // function

    /**
     *
     * @param   array &$p_arrRow
     *
     * @return  array
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs, $g_config;

        $l_dao         = isys_cmdb_dao::instance($this->m_db);
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        // Prevent selection of archivied or deleted assignements as primary.
        if ($_SESSION['cRecStatusListView'] == C__RECORD_STATUS__NORMAL)
        {
            if (isys_auth_cmdb::instance()
                ->has_rights_in_obj_and_category(isys_auth::EDIT, $p_arrRow["isys_catg_contact_list__isys_obj__id"], 'C__CATG__CONTACT')
            )
            {
                $l_onclick = "window.toggle_primary_contact(this, " . ((int) $p_arrRow["isys_catg_contact_list__id"]) . ", " . ((int) $p_arrRow["isys_catg_contact_list__isys_obj__id"]) . ");";

                if ($p_arrRow['isys_catg_contact_list__primary_contact'] > 0)
                {
                    $p_arrRow["contact_primary"] = '<button class="btn btn-small primary-button green" type="button" onclick="' . $l_onclick . '">
						<img class="mr5" src="' . $g_dirs['images'] . 'icons/silk/tick.png" title="' . _L("LC__CATG__CONTACT_LIST__MARK_AS_PRIMARY") . '" />
						<span>' . _L('LC__UNIVERSAL__YES') . '</span>
						</button>';
                }
                else
                {
                    $p_arrRow["contact_primary"] = '<button class="btn btn-small primary-button red" type="button" onclick="' . $l_onclick . '">
						<img class="mr5" src="' . $g_dirs['images'] . 'icons/silk/cross.png" title="' . _L("LC__CATG__CONTACT_LIST__MARK_AS_PRIMARY") . '" />
						<span>' . _L('LC__UNIVERSAL__NO') . '</span>
						</button>';
                } // if

                // Wrap the button, so that "changing" the state does not make the table bouncy.
                $p_arrRow["contact_primary"] = '<div style="width:75px;">' . $p_arrRow["contact_primary"] . '</div>';
            }
            else
            {
                if ($p_arrRow['isys_catg_contact_list__primary_contact'] > 0)
                {
                    $p_arrRow["contact_primary"] = '<img class="vam mr5" src="' . $g_dirs['images'] . 'icons/silk/tick.png" /><span class="vam">' . _L(
                            'LC__UNIVERSAL__YES'
                        ) . '</span>';
                }
                else
                {
                    $p_arrRow["contact_primary"] = '<img class="vam mr5" src="' . $g_dirs['images'] . 'icons/silk/cross.png" /><span class="vam">' . _L(
                            'LC__UNIVERSAL__NO'
                        ) . '</span>';
                } // if
            } // if
        }
        else
        {
            $p_arrRow["contact_primary"] = $l_empty_value;
        } // if

        $l_params = [
            "p_strPopupType"    => "dialog_plus",
            "p_strSelectedID"   => $p_arrRow["isys_catg_contact_list__isys_contact_tag__id"],
            "p_strTable"        => "isys_contact_tag",
            "p_strClass"        => "input-mini",
            "p_bInfoIconSpacer" => false,
            "name"              => "C__CATG__CONTACT_TAG_" . $this->m_i++,
            "p_onChange"        => "new Ajax.Updater('infoBox', '?ajax=1&call=update_contact_tag&" . C__CMDB__GET__OBJECT . "=" . $_GET[C__CMDB__GET__OBJECT] . "', { parameters: " . "{ conId:'" . $p_arrRow["isys_catg_contact_list__id"] . "', valId:this.value}, method:'post', onComplete:function(){ $('infoBox').highlight();}});",
            'p_bEditMode'       => true
        ];

        $l_obj_type_arr = $l_dao->get_objtype($p_arrRow["isys_obj__isys_obj_type__id"])
            ->get_row();

        if (empty($l_obj_type_arr["isys_obj_type__icon"]))
        {
            $l_obj_type_arr["isys_obj_type__icon"] = $g_dirs['images'] . 'tree/person_intern.gif';
        }
        else
        {
            $l_obj_type_arr["isys_obj_type__icon"] = $g_config['www_dir'] . $l_obj_type_arr["isys_obj_type__icon"];
        }

        $p_arrRow["contact_type"] = '<img src="' . $l_obj_type_arr["isys_obj_type__icon"] . '" class="vam" title="' . _L(
                $l_obj_type_arr["isys_obj_type__title"]
            ) . '" /> <span class="vam">' . _L($l_obj_type_arr["isys_obj_type__title"]) . '</span>';

        $p_arrRow["contact_tag"] = (new isys_smarty_plugin_f_popup)->set_parameter($l_params);

        $p_arrRow["contact_mail"] = $l_empty_value;
        if (!empty($p_arrRow["mail_address"]))
        {
            $p_arrRow["contact_mail"] = '<a href="' . isys_helper_link::create_mailto($p_arrRow["mail_address"]) . '" target="_blank">' . $p_arrRow["mail_address"] . '</a>';
        }

        $p_arrRow["contact_name"] = $p_arrRow["isys_obj__title"];

        if ($p_arrRow["isys_cats_person_list__id"])
        {
            $p_arrRow["contact_department"] = $p_arrRow["isys_cats_person_list__department"];

            if (!empty($p_arrRow["isys_cats_person_list__first_name"]) && !empty($p_arrRow["isys_cats_person_list__last_name"]))
            {
                $p_arrRow["contact_name"] = $p_arrRow["isys_cats_person_list__first_name"] . " " . $p_arrRow["isys_cats_person_list__last_name"];
            } // if

            $p_arrRow["contact_telephone"] = (!empty($p_arrRow["isys_cats_person_list__phone_company"])) ? (_L(
                    "LC__CONTACT__PERSON_TELEPHONE_COMPANY"
                ) . ": <b>" . $p_arrRow["isys_cats_person_list__phone_company"] . "</b>") : ((!empty($p_arrRow["isys_cats_person_list__phone_mobile"])) ? (_L(
                    "LC__CONTACT__PERSON_TELEPHONE_MOBILE"
                ) . ": <b>" . $p_arrRow["isys_cats_person_list__phone_mobile"] . "</b>") : ((!empty($p_arrRow["isys_cats_person_list__phone_home"])) ? (_L(
                    "LC__CONTACT__PERSON_TELEPHONE_HOME"
                ) . ": <b>" . $p_arrRow["isys_cats_person_list__phone_home"] . "</b>") : $l_empty_value));

            if ($p_arrRow["isys_cats_person_list__isys_connection__id"] > 0)
            {
                $l_dao = new isys_cmdb_dao_connection($this->get_database_component());
                $l_row = $l_dao->get_connection($p_arrRow["isys_cats_person_list__isys_connection__id"])
                    ->get_row();

                $p_arrRow["contact_organization"] = $l_dao->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);
                $p_arrRow["contact_organization"] = '<a href="' . isys_helper_link::create_url(
                        [C__CMDB__GET__OBJECT => $l_row["isys_connection__isys_obj__id"]]
                    ) . '">' . $p_arrRow["contact_organization"] . '</a>';
            }
            else
            {
                $p_arrRow["contact_organization"] = _L("LC__CATG__CONTACT_LIST__NO_ORGANISATION_ASSIGNED");
            } // if
        }
        else if ($p_arrRow["isys_cats_person_group_list__id"])
        {
            if (!empty($p_arrRow["isys_cats_person_group_list__title"]))
            {
                $p_arrRow["contact_name"] = $p_arrRow["isys_cats_person_group_list__title"];
            } // if

            $p_arrRow["contact_telephone"]    = $p_arrRow["isys_cats_person_group_list__phone"];
            $p_arrRow["contact_organization"] = $l_empty_value;
        }
        else if ($p_arrRow["isys_cats_organization_list__id"])
        {
            if (!empty($p_arrRow["isys_cats_organization_list__title"]))
            {
                $p_arrRow["contact_name"] = $p_arrRow["isys_cats_organization_list__title"];
            } // if

            $p_arrRow["contact_telephone"] = $p_arrRow["isys_cats_organization_list__telephone"];

            if ($p_arrRow["isys_cats_organization_list__isys_connection__id"] > 0)
            {
                $l_dao = new isys_cmdb_dao_connection($this->get_database_component());
                $l_row = $l_dao->get_connection($p_arrRow["isys_cats_organization_list__isys_connection__id"])
                    ->get_row();

                $p_arrRow["contact_organization"] = $l_dao->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);
                $p_arrRow["contact_organization"] = '<a href="' . isys_helper_link::create_url(
                        [C__CMDB__GET__OBJECT => $l_row["isys_connection__isys_obj__id"]]
                    ) . '">' . $p_arrRow["contact_organization"] . '</a>';
            }
            else
            {
                $p_arrRow["contact_organization"] = _L('LC__CATG__CONTACT_LIST__NO_ORGANISATION_ASSIGNED');
            } // if
        } // if

        $p_arrRow["contact_name"] = '<a href="' . isys_helper_link::create_url(
                [C__CMDB__GET__OBJECT => $p_arrRow["isys_obj__id"]]
            ) . '">' . $p_arrRow["contact_name"] . '</a>';
    } // function

    /**
     * Method for retrieving the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'contact_name'         => 'LC__CATG__CONTACT_LIST__NAME',
            'contact_type'         => 'LC__CATG__CONTACT_LIST__TYPE',
            'contact_department'   => 'LC__CONTACT__PERSON_DEPARTMENT',
            'contact_mail'         => 'LC__CONTACT__PERSON_MAIL_ADDRESS',
            'contact_telephone'    => 'LC__CATG__CONTACT_LIST__PHONE',
            'contact_organization' => 'LC__CATG__CONTACT_LIST__ASSIGNED_ORGANISATION',
            'contact_tag'          => 'LC__CMDB__CONTACT_ROLE',
            'contact_primary'      => 'LC__CATG__CONTACT_LIST__PRIMARY'
        ];
    } // function

    /**
     * Probably unused method.
     *
     * @return  string
     */
    public function make_row_link()
    {
        return '#';
    } // function
} // class