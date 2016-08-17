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

define('CONST_FILES_RECURSIVE', 1);
define('CONST_FILES_NORECURSIVE', 0);

define('C_FILES__MODE_DOWNLOAD', "binary");
define('C_FILES__MODE_TEXT', "text");
define('C_FILES__MODE_VIEW', "image");

/**
 * i-doit
 *
 * Files
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_filemanager extends isys_component
{
    /**
     * @var  array
     */
    private static $m_errors;

    /**
     * @var  array
     */
    private $m_disallowed_ext;

    /**
     * @var  boolean
     */
    private $m_logging;

    /**
     * @var  string
     */
    private $m_target_dir;

    /**
     * @var  array
     */
    private $m_warnings;

    /**
     * Returns true/false if errors occured.
     *
     * @return  boolean
     */
    public static function are_errors_occured()
    {
        return (count(self::$m_errors > 0));
    } // function

    /**
     * Get errors.
     *
     * @return  array
     */
    public static function get_errors()
    {
        return (self::are_errors_occured() ? self::$m_errors : false);
    } // function

    /**
     * Member of isys_component_filemanager.
     *
     * @static
     *
     * @param   string  $p_filename
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author Dennis Stücken <dstuecken@synetics.de>
     */
    public static function create_new_filename($p_filename, $p_obj_id)
    {
        return $p_obj_id . "__" . time() . "__" . isys_helper_upload::prepare_filename($p_filename);
    } // function

    /**
     * Returns true/false if warnings occured.
     *
     * @return  boolean
     */
    public function are_warnings_occured()
    {
        return (count($this->m_warnings > 0));
    } // function

    /**
     * Add an error.
     *
     * @param   string $p_error
     *
     * @return  isys_component_filemanager
     */
    public function _add_error($p_error)
    {
        self::$m_errors[] = $p_error;

        return $this;
    } // function

    /**
     * Add an error.
     *
     * @param   string $p_warning
     *
     * @return  isys_component_filemanager
     */
    public function _add_warning($p_warning)
    {
        $this->m_warnings[] = $p_warning;

        return $this;
    } // function

    /**
     * Get warnings.
     *
     * @return  array
     */
    public function get_warnings()
    {
        return ($this->are_warnings_occured() ? $this->m_warnings : false);
    } // function

    /**
     * Member of isys_component_filemanager.
     *
     * @param   array $p_filesArray
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function is_uplaoded_file($p_filesArray)
    {
        if (!$l_tmpfile = get_cfg_var('upload_tmp_dir'))
        {
            $l_tmpfile = dirname(tempnam('', ''));
        } // if

        $l_tmpfile .= '/' . basename($p_filesArray["tmp_name"]);

        return (preg_replace('/+', '/', $l_tmpfile) == $p_filesArray["tmp_name"]);
    } // function

    /**
     * ################################### *
     *  FILE DOWNLOAD FUNCTIONS            *
     *                                     *
     * ################################### *
     */

    /**
     * Uploads a file to a directory.
     *
     * @param   array  $p_filesArray   Information about the uploading file.
     * @param   string $p_new_filename (optional) Write file's content to this file.
     *
     * @return  string  Name of uploaded file
     */
    public function receive($p_filesArray, $p_new_filename = null)
    {
        $l_filepath     = $p_filesArray['tmp_name'];
        $l_upload_err   = $p_filesArray['error'];

        $l_new_filename = $p_new_filename === null ? $p_filesArray['name'] : $p_new_filename; // if

        if (!$l_upload_err)
        {
            if (file_exists($l_filepath))
            {
                $l_move_file = move_uploaded_file($l_filepath, $this->m_target_dir . $l_new_filename);

                if ($l_move_file == false)
                {
                    $this->_add_error(
                        _L(
                            'LC__UNIVERSAL__FILE_UPLOAD__MOVE_UPLOADED_FILE_FAILED',
                            [
                                $l_filepath,
                                $this->m_target_dir
                            ]
                        )
                    );

                    return false;
                } // if

                return true;
            }

            throw new ErrorException(_L('LC__UNIVERSAL__FILE_UPLOAD__COMMON_PROBLEM'));
        }
        else
        {
            switch ($l_upload_err)
            {
                case 1:
                    $l_strupload_err = _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_SIZE_TOO_BIG', $p_filesArray['size']);
                    break;
                case 2:
                    $l_strupload_err = _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_SIZE_TOO_BIG', $p_filesArray['size']);
                    break;
                case 3:
                    $l_strupload_err = _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_UPLOADED_PARTIALLY');
                    break;
                case 4:
                    $l_strupload_err = _L('LC__UNIVERSAL__FILE_UPLOAD__NO_FILE_SELECTED');
                    break;
                default:
                    $l_strupload_err = _L('LC__UNIVERSAL__FILE_UPLOAD__COMMON_PROBLEM');
                    break;
            } // switch

            $this->_add_error($l_strupload_err);

            return false;
        } // if
    } // function

    /**
     * ################################### *
     *  FILE HANDLING                      *
     *                                     *
     * ################################### *
     */

    /**
     * Sends a specified file as a new header to the user's browser.
     *
     * @param   string $p_filename
     * @param   object $p_daoFile
     * @param   string $p_mode
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @return boolean
     */
    public function send($p_filename, $p_daoFile = null, $p_mode = C_FILES__MODE_DOWNLOAD)
    {
        // Clear file stat cache.
        clearstatcache();
        $l_content_disposition = '';

        //$p_filename = preg_replace('/[^\x30-\x39\x41-\x5A\._-]*/', '', $p_filename);
        $p_filename = str_replace(
            [
                '../',
                '..\\'
            ],
            '',
            $p_filename
        );

        if (is_object($p_daoFile))
        {
            $l_arFile        = $p_daoFile->get_row();
            $l_physical_file = $this->m_target_dir . $l_arFile["isys_physical_filename"];
            $l_filename      = $l_arFile["isys_filename"];
        }
        else
        {
            if ($p_mode == C_FILES__MODE_DOWNLOAD)
            {
                $l_tmp                 = explode(DS, $p_filename);
                $l_content_disposition = "Content-Disposition: attachment; filename=\"" . htmlentities(end($l_tmp)) . "\";";
            } // if

            $l_filename = !file_exists($p_filename) ? $this->m_target_dir . $p_filename : $p_filename;

            $l_physical_file = $l_filename;
        } // if

        // @todo DS: I know this is really cruel, but if someone clicks on the download button, we need this MIME-Type!
        $l_mimetype = "application/octet-stream";

        if (!is_file($l_physical_file))
        {
            return false;
        } // if

        if (!is_readable($l_physical_file))
        {
            $this->_add_error("File: " . $l_physical_file . " exists but is not readable");

            return false;
        } // if

        /**
         * @desc download/view:
         */
        set_time_limit(0);
        ob_end_clean();
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: " . gmdate("D, d M Y H:i:s", (time() + 2 * isys_convert::HOUR)) . " GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Content-Type: " . $l_mimetype);
        header("Content-length: " . filesize($l_physical_file));
        header("Content-Disposition: inline; filename=\"" . $l_filename . "\"");
        header("Content-transfer-encoding: binary");

        if (!empty($l_content_disposition))
        {
            header($l_content_disposition);
        } // if

        if (!is_file($l_physical_file) or connection_status() != 0)
        {
            return false;
        } // if

        if ($l_fp = fopen($l_physical_file, 'rb'))
        {
            while (!feof($l_fp) and (connection_status() == 0))
            {
                echo fread($l_fp, 1024 * 8);
                flush();
            } // while

            fclose($l_fp);
        } // if

        die;
    } // function

    /**
     * Setter for "disallowed" filetypes.
     *
     * @param   array $p_filetypes
     *
     * @return  isys_component_filemanager
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function set_disallowed_filetypes(array $p_filetypes = [])
    {
        $this->m_disallowed_ext = $p_filetypes;

        return $this;
    } // function

    /**
     * Getter for "disallowed" filetypes.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_disallowed_filetypes()
    {
        return $this->m_disallowed_ext;
    } // function

    /**
     * Method for checking, if the given filetype is allowed to be handled.
     *
     * @param   string $p_filetype
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function check_file_type($p_filetype)
    {
        if (!in_array($p_filetype, $this->get_disallowed_filetypes()))
        {
            $this->_add_error('Filetype ".' . $p_filetype . '" not allowed');

            return false;
        } // if

        return true;
    } // function

    /**
     * Method for (re-) setting the upload path.
     *
     * @param   string $p_directory
     *
     * @return  isys_component_filemanager
     */
    public function set_upload_path($p_directory)
    {
        if (!is_dir($p_directory))
        {
            $this->_add_error("Directory " . $p_directory . " is not existing!");
        } // if

        if (!is_readable($p_directory))
        {
            $this->_add_error("Directory " . $p_directory . " is not readable!");
        } // if

        if (!is_writeable($p_directory))
        {
            $this->_add_error("Directory " . $p_directory . " is not writeable!");
        } // if

        $this->m_target_dir = $p_directory;

        if (!$this->m_target_dir)
        {
            throw new Exception('Error: File upload target dir is not set.');
        }
        if (!file_exists($this->m_target_dir))
        {
            throw new Exception('Error: File upload target dir does not exist.');
        }

        return $this;
    } // function

    /**
     * Method for deleting a given file.
     *
     * @param   string $p_file
     * @param   string $p_dir
     *
     * @return  boolean
     */
    public function delete($p_file, $p_dir = null)
    {
        if ($p_dir === null)
        {
            $l_dir = $this->get_upload_path();
        }
        else
        {
            $l_dir = $p_dir;
        } // if

        $l_dir = rtrim($l_dir, DS . '/');

        // This is necessary, if "$p_file" already contains the path.
        if (!empty($l_dir))
        {
            $l_dir .= DS;
        } // if

        if (file_exists($l_dir . $p_file))
        {
            return unlink($l_dir . $p_file);
        } // if

        return true;
    } // function

    /**
     * Returns directory where the manager is about to upload to
     *
     * @return string _target_dir
     */
    function get_upload_path()
    {
        return $this->m_target_dir;
    } // function

    /**
     * Copy files recursively.
     *
     * @param   string $p_source
     * @param   string $p_dest
     *
     * @return  mixed
     */
    public function copy_files($p_source, $p_dest)
    {
        if (is_dir($p_source))
        {
            $l_folder = opendir($p_source);
            while ($l_file = readdir($l_folder))
            {
                if ($l_file == '.' || $l_file == '..')
                {
                    continue;
                } // if

                if (is_dir($p_source . '/' . $l_file))
                {
                    mkdir($p_dest . '/' . $l_file, 0777);
                    $this->copy_files($p_source . '/' . $l_file, $p_dest . '/' . $l_file);
                }
                else
                {
                    copy($p_source . '/' . $l_file, $p_dest . '/' . $l_file);
                } // if
            } // while

            closedir($l_folder);

            return true;
        }
        else
        {
            copy($p_source, $p_dest);
        } // if
    } // function

    /**
     * Delete files and their sub-directories recursively.
     *
     * @param   string  $p_source
     * @param   integer $p_opt
     *
     * @return boolean
     */
    public function remove_files($p_source, $p_opt = CONST_FILES_RECURSIVE)
    {
        if (is_dir($p_source))
        {
            $l_folder = opendir($p_source);
            while ($l_file = readdir($l_folder))
            {
                if ($l_file == '.' || $l_file == '..')
                {
                    continue;
                } // if

                if (is_dir($p_source . '/' . $l_file) && $p_opt == CONST_FILES_RECURSIVE)
                {
                    $this->remove_files($p_source . '/' . $l_file);
                }
                else
                {
                    unlink($p_source . '/' . $l_file);
                } // if
            } // while

            closedir($l_folder);
            rmdir($p_source);
        }
        else
        {
            if (file_exists($p_source))
            {
                unlink($p_source);
            } // if
        } // if

        return true;
    } // function

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $g_dirs;

        // Setting some INI configurations.
        ini_set("upload_max_filesize", "100M");
        ini_set("file_uploads", "On");
        ini_set("upload_tmp_dir", $g_dirs["fileman"]["temp_dir"]);

        // Preparing some variables.
        self::$m_errors         = [];
        $this->m_warnings       = [];
        $this->m_logging        = false;
        $this->m_disallowed_ext = ["exe"];
        $this->m_target_dir     = $g_dirs["fileman"]["target_dir"];
    } // function
} // class