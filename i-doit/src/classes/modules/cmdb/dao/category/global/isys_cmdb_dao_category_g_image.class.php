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
 * DAO: global category for object images
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_image extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'image';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for property assigned_variant.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_image_selection()
    {
        global $g_absdir;
        $l_uploadedImages = [];
        if (file_exists($g_absdir . DS . 'upload' . DS . 'images') && is_dir($g_absdir . DS . 'upload' . DS . 'images'))
        {
            $l_directory = dir($g_absdir . DS . 'upload' . DS . 'images');
            while ($l_file = $l_directory->read())
            {
                if (strpos($l_file, '.') !== 0)
                {
                    $l_uploadedImages[$l_file] = $l_file;
                } // if
            } // while
        } // if
        return $l_uploadedImages;
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_image_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_image_list__isys_obj__id
			WHERE TRUE $p_condition " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND (isys_catg_image_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_catg_image_list__status = " . $this->convert_sql_int($p_status) . ") ";
        } // if

        return $this->retrieve($l_sql . ";");
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'image_selection' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IMAGE_UPLOADED_IMAGES',
                        C__PROPERTY__INFO__DESCRIPTION => 'Image selection'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_image_list__image_link'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__IMAGE_TITLEX',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_image',
                                    'callback_property_image_selection'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                        C__PROPERTY__PROVIDES__VIRTUAL    => true
                    ]
                ]
            ),
            'image'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::upload(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IMAGE_OBJ_FILE',
                        C__PROPERTY__INFO__DESCRIPTION => 'File'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_image_list__image_link'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__IMAGE_TITLEX'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object_image'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT  => false,
                        C__PROPERTY__PROVIDES__LIST    => false,
                        C__PROPERTY__PROVIDES__VIRTUAL => true
                    ]
                ]
            ),
            'description'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_image_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__IMAGE
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            )
        ];
    } // function

    /**
     * Sync-method.
     *
     * @param   integer $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            if (($l_image = $p_category_data['properties']['image_selection'][C__DATA__VALUE]) === null)
            {
                $l_image = $p_category_data['properties']['image'][C__DATA__VALUE];
            } // if
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $this->create(
                            $p_object_id,
                            $l_image,
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    } // if
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            $l_image,
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            } // switch
        }

        return false;
    } // function

    /**
     * Gets the image name from database by obeject id.
     *
     * @param   integer $p_id
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_image_name_by_object_id($p_id)
    {
        $l_sql = "SELECT isys_catg_image_list__image_link AS image
			FROM isys_catg_image_list
			WHERE isys_catg_image_list__isys_obj__id = " . $this->convert_sql_id($p_id) . ";";

        return $this->retrieve($l_sql)
            ->get_row_value('image');
    } // function

    /**
     * Delete method.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete($p_id = null, $p_image_file = null)
    {
        global $g_dirs;

        if ($p_id === null && $p_image_file === null) return false;

        $l_filemanager = new isys_component_filemanager();
        $l_file_name   = [];

        $l_sql = 'DELETE FROM isys_catg_image_list';;

        if ($p_id !== null)
        {
            $l_data                                                   = $this->get_data($p_id)
                ->__to_array();
            $l_file_name[$l_data['isys_catg_image_list__image_link']] = $l_data['isys_catg_image_list__image_link'];

            $l_sql .= ' WHERE (isys_catg_image_list__id = ' . $this->convert_sql_id($p_id) . ');';
        } // if

        if ($p_image_file !== null)
        {
            // Check if other objects have this image
            $l_file_name[$p_image_file] = $p_image_file;

            $l_sql = 'SELECT isys_catg_image_list__id FROM isys_catg_image_list ' . 'WHERE isys_catg_image_list__image_link = ' . $this->convert_sql_text($p_image_file) . ';';

            $l_amount = $this->retrieve($l_sql)
                ->num_rows();

            if ($l_amount == 0)
            {
                foreach ($l_file_name AS $l_file)
                {
                    $l_filemanager->delete($l_file, $g_dirs['fileman']['image_dir']);
                } // foreach
                return true;
            } // if
        } // if

        if ($this->update($l_sql))
        {
            return $this->apply_update();
        } // if

        return false;
    } // function

    /**
     * Saves the uploaded image.
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     * @return  boolean
     */
    public function upload_image()
    {
        global $g_dirs;

        $l_filemanager = new isys_component_filemanager();
        $l_image_name  = $l_filemanager->create_new_filename($_FILES["C__CATG__IMAGE_UPLOAD"]['name'], $this->m_object_id);

        if (is_dir($g_dirs["fileman"]["image_dir"]))
        {
            $l_filemanager->set_upload_path($g_dirs["fileman"]["image_dir"]);
            if ($l_filemanager->receive($_FILES["C__CATG__IMAGE_UPLOAD"], $l_image_name))
            {
                return $l_image_name;
            }
            else
            {
                return false;
            } // if
        }
        else
        {
            $l_dirlen = strlen($g_dirs["fileman"]["image_dir"]);
            $l_dir    = ($l_dirlen > 35) ? ".." . substr($g_dirs["fileman"]["image_dir"], $l_dirlen - 35, $l_dirlen) : $g_dirs["fileman"]["image_dir"];
            $this->display_error("The Directory " . $l_dir . " does not exist. Correct your config.inc.php or create it.");
        } // if

        return false;
    } // function

    /**
     * Save global category image element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @throws  isys_exception_dao_cmdb
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $p_intOldRecStatus = $l_catdata["isys_catg_image_list__status"];
        $this->m_object_id = $_GET[C__CMDB__GET__OBJECT];
        $l_image_file      = null;

        if (!empty($_POST['C__CATG__IMAGE_SELECTION']) && $_POST['C__CATG__IMAGE_SELECTION'] != '-1' && $_FILES['C__CATG__IMAGE_UPLOAD']['name'] == '')
        {
            $l_image_file = str_replace('upload' . DS . 'images' . DS, '', $_POST['C__CATG__IMAGE_SELECTION']);
        } // if

        if ($l_image_file === null)
        {
            $l_image_file = $this->upload_image();
        } // if

        if ($l_catdata['isys_catg_image_list__id'] != "")
        {
            $this->save($l_catdata["isys_catg_image_list__id"], $l_image_file, $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);
            $this->m_strLogbookSQL = $this->get_last_query();
        }
        else
        {
            $l_id = $this->create($this->m_object_id, $l_image_file, $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

            if ($l_id != false)
            {
                $this->m_strLogbookSQL = $this->get_last_query();
            } // if
        } // if

        return null;
    } // function

    /**
     * Updates the current image.
     *
     * @param   integer $p_cat_level
     * @param   string  $p_link
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_link, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_image_list SET
			isys_catg_image_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_image_list__image_link  = " . $this->convert_sql_text($p_link) . ",
			isys_catg_image_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			WHERE isys_catg_image_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    } // function

    /**
     * Creates a new image.
     *
     * @param   int    $p_object_id
     * @param   string $p_link
     * @param   string $p_description
     *
     * @return  mixed   The newly created ID (integer) or boolean false.
     */
    public function create($p_object_id, $p_link, $p_description)
    {
        $l_id = $this->create_connector('isys_catg_image_list', $p_object_id);
        if ($this->save($l_id, $p_link, $p_description))
        {
            return $l_id;
        }
        return false;
    } // function

    /**
     * Displays the occurred error
     *
     * @param  string $p_error
     */
    private function display_error($p_error)
    {
        isys_component_template_infobox::instance()
            ->set_message(
                "<b>" . $p_error . "</b>",
                null,
                null,
                null,
                C__LOGBOOK__ALERT_LEVEL__3
            );
    } // function
} // class