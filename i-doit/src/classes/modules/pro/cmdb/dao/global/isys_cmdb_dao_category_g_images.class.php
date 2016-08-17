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
 * DAO: class for global category "images".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.6
 */
class isys_cmdb_dao_category_g_images extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'images';

    /**
     * Method for retrieving all category properties.
     *
     * @return  array
     */
    public function properties()
    {
        return [
            'name' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__IMAGES__FILENAME',
                        C__PROPERTY__INFO__DESCRIPTION => 'File name'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_images_list__filename'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__MANDATORY => true
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__IMPORT => true,
                        C__PROPERTY__PROVIDES__EXPORT => true,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__VIRTUAL => true
                    ]
                ]
            ),
            'content' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::upload(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__IMAGES__FILECONTENT',
                        C__PROPERTY__INFO__DESCRIPTION => 'File content'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_images_list__filecontent'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__VIRTUAL => true
                    ]
                ]
            )
        ];
    } // function

    /**
     * Method for retrieving image IDs by a given object ID. This should be MUCH faster than the "get_data" method.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_images_by_object($p_object_id)
    {
        $l_sql = 'SELECT isys_catg_images_list__id, isys_catg_images_list__filename, isys_catg_images_list__filemime
			FROM isys_catg_images_list
			WHERE isys_catg_images_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
			AND isys_catg_images_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			ORDER BY isys_catg_images_list__id ASC;';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving the first, last or every image of an object.
     *
     * @param   integer $p_object_id
     * @param   string  $p_which Define, which images to get. Possible values: "all", "first" and "last".
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_certain_images_by_object($p_object_id, $p_which = null)
    {
        $l_sql = 'SELECT *
			FROM isys_catg_images_list
			WHERE isys_catg_images_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
			AND isys_catg_images_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        switch ($p_which)
        {
            case 'first':
                $l_sql .= ' ORDER BY isys_catg_images_list__id ASC LIMIT 1;';
                break;

            case 'last':
                $l_sql .= ' ORDER BY isys_catg_images_list__id DESC LIMIT 1;';
                break;

            default:
            case 'all':
                $l_sql .= ' ORDER BY isys_catg_images_list__id ASC;';
        } // switch

        return $this->retrieve($l_sql);
    } // function

    /**
     * @param   integer $p_object_id
     *
     * @return  array
     * @throws  isys_exception_filesystem
     */
    public function handle_upload($p_object_id)
    {
        global $g_dirs;

        $l_file_prefix = uniqid();
        $l_upload_dir  = realpath($g_dirs["temp"]) . DS;
        $l_uploader    = new isys_library_fileupload;
        $l_filename    = $l_uploader->getName();

        $l_result = $l_uploader->set_prefix($l_file_prefix)
            ->handleUpload($l_upload_dir);

        if ($l_result['success'] !== true || (isset($l_result['error']) && !empty($l_result['error'])))
        {
            throw new isys_exception_filesystem($l_result['error']);
        } // if

        // If the "just uploaded" file could not be found, we throw an error.
        if (!file_exists($l_upload_dir . $l_file_prefix . $l_filename))
        {
            throw new isys_exception_filesystem($l_result['error']);
        } // if

        $l_file_content = file_get_contents($l_upload_dir . $l_file_prefix . $l_filename);

        $l_mimetypes = isys_helper::get_image_mimetypes();

        $l_fileextension = substr(strrchr($l_filename, '.'), 1);

        $l_file_mime = (isset($l_mimetypes[$l_fileextension]) ? $l_mimetypes[$l_fileextension] : null);

        $l_result['data'] = $this->create_image($p_object_id, $l_filename, $l_file_content, $l_file_mime);

        // If the image has been stored in the DB we delete the temp-image.
        if ($l_result['data'] > 0)
        {
            $l_filemanager = new isys_component_filemanager();
            $l_filemanager->delete($l_file_prefix . $l_filename, $l_upload_dir);
        } // if

        return $l_result;
    } // function

    /**
     * Method for loading a given image from the database.
     *
     * @param   integer $p_image_id
     *
     * @return  string
     */
    public function load_image($p_image_id)
    {
        $l_image = base64_decode(
            $this->get_data($p_image_id)
                ->get_row_value('isys_catg_images_list__filecontent')
        );

        $l_imagesize = getimagesizefromstring($l_image);

        // This might not be correct... Firefox cuts the images!
        //header('Content-Length: ' . isys_strlen($l_image));
        header('Content-Type: ' . $l_imagesize['mime']);

        return $l_image;
    } // funtion

    /**
     * Method for saving a image in the database.
     *
     * @param   integer $p_object_id
     * @param   string  $p_filename
     * @param   string  $p_filecontent
     * @param   string  $p_filemime
     *
     * @return  mixed
     * @throws  isys_exception_dao
     */
    public function create_image($p_object_id, $p_filename, $p_filecontent, $p_filemime)
    {
        $l_sql = 'INSERT INTO isys_catg_images_list SET
			isys_catg_images_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
			isys_catg_images_list__uploaded = NOW(),
			isys_catg_images_list__filename = ' . $this->convert_sql_text(isys_glob_replace_accent($p_filename)) . ',
			isys_catg_images_list__filecontent = ' . $this->convert_sql_text(base64_encode($p_filecontent)) . ',
			isys_catg_images_list__filemime = ' . $this->convert_sql_text($p_filemime) . ',
			isys_catg_images_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_sql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Method for deleting a image from i-doit.
     *
     * @param   integer $p_image_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function delete_image($p_image_id)
    {
        return $this->update('DELETE FROM isys_catg_images_list WHERE isys_catg_images_list__id = ' . $this->convert_sql_id($p_image_id) . ';') && $this->apply_update();
    } // function
} // class