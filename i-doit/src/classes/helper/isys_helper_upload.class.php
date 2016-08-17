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
 * Helper methods for uploading.
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_helper_upload
{
    public static $m_default_directory = 'upload';

    /**
     * This method returns an error-message, corresponding to the given file.
     *
     * @static
     *
     * @param   string $p_file The name of the upload-field (f.e. "import_file").
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_error($p_file)
    {
        switch ($_FILES[$p_file]['error'])
        {
            case UPLOAD_ERR_INI_SIZE:
                return _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_SIZE_TOO_BIG');

            case UPLOAD_ERR_FORM_SIZE:
                return _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_SIZE_TOO_BIG');

            case UPLOAD_ERR_PARTIAL:
                return _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_UPLOADED_PARTIALLY');

            case UPLOAD_ERR_NO_FILE:
                return _L('LC__UNIVERSAL__FILE_UPLOAD__NO_FILE_SELECTED');

            case UPLOAD_ERR_NO_TMP_DIR:
                return _L('LC__UNIVERSAL__FILE_UPLOAD__NO_TMP_DIR');

            case UPLOAD_ERR_CANT_WRITE:
                return _L('LC__UNIVERSAL__FILE_UPLOAD__NO_WRITE_PERMISSIONS');

            case UPLOAD_ERR_EXTENSION:
                return _L('LC__UNIVERSAL__FILE_UPLOAD__EXTENSION_ERROR');

            default:
            case UPLOAD_ERR_OK:
                return false;
        } // switch
    } // function

    /**
     * Save the uploaded file
     *
     * @static
     *
     * @param   array   $p_file      Must be the file array (f.e. "$_FILES['import_file']").
     * @param   string  $p_filename  The desired filename.
     * @param   string  $p_directory The desired destination.
     * @param   integer $p_chmod
     *
     * @throws  isys_exception_filesystem
     * @return  mixed  On success: string with the full path, on failure: boolean false.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function save(array $p_file, $p_filename = null, $p_directory = null, $p_chmod = 0777)
    {
        if (!isset($p_file['tmp_name']) || !is_uploaded_file($p_file['tmp_name']))
        {
            return false;
        } // if

        if ($p_filename === null)
        {
            $p_filename = $p_file['name'];
        } // if

        if ($p_directory === null)
        {
            // Use the pre-configured upload directory
            $p_directory = self::$m_default_directory;
        } // if

        if (!is_dir($p_directory) || !is_writable(realpath($p_directory)))
        {
            throw new isys_exception_filesystem('Directory "' . $p_directory . '" must be writable', 'The given directory does not exist or is not writable for PHP scripts.');
        } // if

        $l_filename = realpath($p_directory) . DS . $p_filename;

        if (move_uploaded_file($p_file['tmp_name'], $l_filename))
        {
            if ($p_chmod !== false)
            {
                chmod($l_filename, $p_chmod);
            } // if

            // Return new file path
            return $l_filename;
        } // if

        return false;
    } // function

    /**
     * Returns the max. allowed upload file size in Bytes.
     *
     * @static
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_max_upload_size()
    {
        // For setting the size limit, we read the ini-configurations.
        $l_post_size   = isys_convert::to_bytes(ini_get('post_max_size'));
        $l_upload_size = isys_convert::to_bytes(ini_get('upload_max_filesize'));

        // Choose the smaller value for "size limitation".
        if ($l_post_size > $l_upload_size)
        {
            return $l_upload_size;
        } // if

        return $l_post_size;
    } // function

    /**
     * This method will prepare a proper filename without special characters or umlaute.
     *
     * @static
     *
     * @param   string $p_filename
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function prepare_filename($p_filename)
    {
        return preg_replace(
            [
                '/[^a-zA-Z0-9_\.-]/',
                '/[-]+/',
                '/^-|-$/'
            ],
            [
                '-',
                '-',
                ''
            ],
            isys_glob_replace_accent(strtolower($p_filename))
        );
    } // function

    /**
     * This method will append a 8-character long hash to the given filename.
     *
     * @static
     *
     * @param   string  $p_filename
     * @param   boolean $p_prepare_filename
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function append_hash_prefix($p_filename, $p_prepare_filename = true)
    {
        $l_extension = strrchr($p_filename, '.');
        $p_filename  = substr($p_filename, 0, strlen($l_extension) * -1);

        return substr(md5($p_filename . microtime(true)), 0, 8) . '_' . ($p_prepare_filename ? static::prepare_filename($p_filename) : $p_filename) . $l_extension;
    } // function
} // class