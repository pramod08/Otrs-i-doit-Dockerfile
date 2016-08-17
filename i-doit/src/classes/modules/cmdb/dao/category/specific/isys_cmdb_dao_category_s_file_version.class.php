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
 * DAO: specific category for file versions.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_file_version extends isys_cmdb_dao_category_s_file
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'file_version';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__FILE_VERSIONS';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'file_title';

    /**
     * Category's list DAO.
     *
     * @var  string
     */
    protected $m_list = 'isys_cmdb_dao_list_cats_file_version';

    /**
     * Category's table
     *
     * @var  string
     */
    protected $m_table = 'isys_file_version';

    /**
     * Category's template
     *
     * @var  string
     */
    protected $m_tpl = 'cats__file__version.tpl';

    /**
     * @param string $p_table
     * @param null   $p_obj_id
     *
     * @return null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * Deletes file physically.
     *
     * @param   string $p_strFile
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function delete_file($p_strFile)
    {
        $l_obj = new isys_component_filemanager();

        return $l_obj->delete($p_strFile);
    } // function

    /**
     * Get the number of items.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id))
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        $l_sql = "SELECT count(version.isys_file_version__id) AS count
			FROM isys_file_physical physical
			INNER JOIN isys_file_version version ON version.isys_file_version__isys_file_physical__id = physical.isys_file_physical__id
			WHERE version.isys_file_version__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . "
			AND isys_file_version__status < " . $this->convert_sql_int(C__RECORD_STATUS__DELETED) . ";";

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        return $l_row["count"];
    } // function

    /**
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   integer                 $p_catg_list_id
     * @param   integer                 $p_obj_id
     * @param   string                  $p_condition
     * @param   array                   $p_filter
     * @param   integer                 $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->get_file_by_obj_id($p_obj_id);
    } // function

    /**
     * Set the recstatus to normal.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intRecStatus (reference)
     *
     * @return  string errorText
     */
    public function get_rec_status($p_cat_level = 0, &$p_intRecStatus)
    {
        $p_intRecStatus = $this->get_rec_status_by_id_as_string($_GET["cateID"]);

        return null;
    } // function

    /**
     * Method for returning the properties. Currently only for print view.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'file_physical'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_NAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Filename'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_file_physical__filename_original'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ]
                ]
            ),
            'file_title'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_VERSION_TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_file_version__title'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ],
                ]
            ),
            'revision'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__REVISION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Revision'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_file_version__revision'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ]
                ]
            ),
            'upload_date'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_UPLOAD_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Upload date'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_file_physical__date_uploaded'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ]
                ]
            ),
            'version_description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_VERSION_DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_file_version__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__FILE_VERSION_DESCRIPTION_2'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ]
                ]
            )
        ];
    } // function

    /**
     * Rank records method.
     *
     * @param   mixed $p_ids
     *
     * @return  boolean
     * @author  dennis stuecken <dstuecken@synetics.de>
     */
    public function rank_records($p_ids, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        if (is_array($p_ids))
        {
            foreach ($p_ids as $l_value)
            {
                $this->rank_file_version($l_value);
            } // foreach
        }
        else
        {
            $this->rank_file_version($p_ids);
        } // if

        return true;
    } // function

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        // Only call the parent method, if this is a "create" (and no "update").
        if ($_POST['C__CATS__FILE_VERSION_UPDATE'] != '1')
        {
            return parent::save_element($p_cat_level, $p_intOldRecStatus, $p_create);
        } // if

        $l_sql = 'UPDATE isys_file_version SET
			isys_file_version__title = ' . $this->convert_sql_text($_POST['C__CATS__FILE_VERSION_TITLE']) . ',
			isys_file_version__description = ' . $this->convert_sql_text($_POST['C__CATS__FILE_VERSION_DESCRIPTION']) . '
			WHERE isys_file_version__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__CATLEVEL]) . ';';

        return ($this->update($l_sql) && $this->apply_update()) ? null : -1;
    } // function

    /**
     * Dummy sync.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  null
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        return null;
    } // function

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @return  boolean
     */
    public function validate_user_data()
    {
        $l_retValid         = true;
        $l_arrTomAdditional = [];

        // Only validate, if the version is about to be "created" - On update, we can't upload a new image. So we skip this.
        if (empty($_FILES["C__CATS__FILE_UPLOAD"]["name"]) && $_POST['C__CATS__FILE_VERSION_UPDATE'] != '1')
        {
            $l_arrTomAdditional["C__CATS__FILE_UPLOAD"]["p_strInfoIconError"] = "Please upload a file for this version!";
            $l_retValid                                                       = false;
        } // if

        $this->set_additional_rules(($l_retValid == false) ? $l_arrTomAdditional : null)
            ->set_validation($l_retValid);

        return $l_retValid;
    } // function

    /**
     * Deletes entries in isys_file_physical and isys_file_version and removes the file physically.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    private function rank_file_version($p_id)
    {
        $l_nID     = null;
        $l_bRet    = true;
        $l_strFile = '';

        $l_ret = $this->get_file_by_version_id($p_id);

        if ($l_ret->num_rows() > 0)
        {
            $l_row     = $l_ret->get_row();
            $l_nID     = $l_row['isys_file_physical__id'];
            $l_strFile = $l_row['isys_file_physical__filename'];
        } // if

        if (strlen($l_strFile) > 0)
        {
            $l_bRet = $this->delete_file($l_strFile);
        } // if

        if ($l_bRet)
        {
            $l_bRet = $this->update('DELETE FROM isys_cats_file_list WHERE isys_cats_file_list__isys_file_version__id = ' . $this->convert_sql_id($p_id) . ';');
        } // if

        if ($l_bRet)
        {
            $l_bRet = $this->update('DELETE FROM isys_file_version WHERE isys_file_version__id = ' . $this->convert_sql_id($p_id) . ';');
        } // if

        if ($l_nID && $l_bRet)
        {
            $l_bRet = $this->update('DELETE FROM isys_file_physical WHERE isys_file_physical__id = ' . $this->convert_sql_id($l_nID) . ';');
        } // if

        if ($l_bRet)
        {
            $l_bRet = $this->apply_update();
        } // if

        return $l_bRet;
    } // function
} // class