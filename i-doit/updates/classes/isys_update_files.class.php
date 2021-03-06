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
 * i-doit - Updates
 *
 * @package     i-doit
 * @subpackage  Update
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_update_files extends isys_update
{
    /**
     * The update file-path.
     *
     * @var  string
     */
    private $m_path;

    /**
     * Sets the update file-path.
     *
     * @param   string $p_path
     *
     * @return  boolean
     */
    public function set_path($p_path)
    {
        if (is_dir($p_path))
        {
            $this->m_path = $p_path;

            return true;
        } // if

        return false;
    } // function

    /**
     * Retrieves the update file-path.
     *
     * @return  string
     */
    public function get_path()
    {
        return $this->m_path;
    } // function

    /**
     * Initialize method sets the path where the update files can be found.
     *
     * @param  string $p_path
     */
    public function init($p_path)
    {
        $this->set_path($p_path);
    } // function

    /**
     * Delete defined files
     *
     * @return bool
     */
    public function delete($p_dir = null)
    {
        global $g_upd_dir;
        global $g_absdir;

        $l_log = isys_update_log::get_instance();
        $l_dir = $g_upd_dir;
        if ($p_dir !== null)
        {
            $l_dir = $p_dir;
        } // if

        if ($l_dir)
        {
            if (file_exists($l_dir . "/update_files.xml"))
            {
                $l_files = simplexml_load_file($l_dir . "/update_files.xml");

                $l_log->add("Deleting files", C__MESSAGE, "bold", C__LOW);

                if (isset($l_files->delete->file) && count($l_files->delete->file) > 0)
                {
                    $l_log->add("Deleting " . count($l_files->delete->file) . " outdated files..", C__MESSAGE, "bold");

                    foreach ($l_files->delete->file as $l_file)
                    {
                        $l_id          = null;
                        $l_delete      = (string) $l_file;
                        $l_file_exists = false;

                        if (file_exists($l_delete) && is_file($l_delete))
                        {
                            $l_file_exists = true;
                        }
                        elseif (file_exists($g_absdir . DS . $l_delete) && is_file($g_absdir . DS . $l_delete))
                        {
                            $l_file_exists = true;
                            $l_delete      = $g_absdir . DS . $l_delete;
                        } // if

                        if ($l_file_exists)
                        {
                            if (is_writeable($g_absdir))
                            {
                                $l_id = $l_log->add($l_delete, C__MESSAGE);

                                try
                                {
                                    if (unlink($l_delete))
                                    {
                                        $l_log->result($l_id, C__OK, C__LOW);
                                    }
                                    else
                                    {
                                        throw new ErrorException("Could not delete " . $l_delete);
                                    }
                                }
                                catch (ErrorException $e)
                                {
                                    $l_log->result($l_id, C__ERR, C__MEDIUM);

                                    $l_log->add($e->getMessage() . ' - Please delete manually.', C__MESSAGE, "indent", C__HIGH, C__OK);
                                    $l_log->debug($e->getMessage() . ' - Please delete manually.');
                                }

                            }
                            else
                            {
                                $l_log->add($l_delete . " - No permission", C__MESSAGE, "indent", C__HIGH, C__OK);
                            }
                        }
                        else
                        {
                            //$l_log->add($l_delete . " already deleted", C__MESSAGE, "indent", C__LOW, C__OK);
                        }
                    }
                }
                else
                {
                    $l_log->add("No files to delete this time", C__MESSAGE, "indent", C__LOW, C__OK);
                }

                if (is_writeable($g_absdir))
                {
                    if (isset($l_files->delete->dir) && count($l_files->delete->dir) > 0)
                    {
                        foreach ($l_files->delete->dir as $l_dir)
                        {
                            $l_delete     = (string) $l_dir;
                            $l_dir_exists = false;

                            if (file_exists($l_delete) && is_dir($l_delete))
                            {
                                $l_dir_exists = true;
                            }
                            elseif (file_exists($g_absdir . DS . $l_delete) && is_dir($g_absdir . DS . $l_delete))
                            {
                                $l_dir_exists = true;
                                $l_delete     = $g_absdir . DS . $l_delete;
                            } // if

                            if ($l_dir_exists)
                            {
                                $l_id = $l_log->add($l_delete, C__MESSAGE);

                                try
                                {
                                    if (rmdir($l_delete))
                                    {
                                        $l_log->result($l_id, C__OK, C__MEDIUM);
                                    }
                                    else
                                    {
                                        throw new ErrorException("Could not delete " . $l_delete);
                                    } // if
                                }
                                catch (ErrorException $e)
                                {
                                    $l_log->result($l_id, C__ERR, C__HIGH);
                                    $l_log->add($e->getMessage() . ' - Please delete manually.', C__MESSAGE, "indent", C__HIGH, C__ERR);
                                    $l_log->debug($e->getMessage() . ' - Please delete manually.');
                                }

                            }
                            else
                            {
                                //$l_log->add($l_delete . " already deleted", C__MESSAGE, "indent", C__LOW, C__OK);
                            } // if
                        } // foreach
                    }
                    else
                    {
                        $l_log->add("No directories to delete this time", C__MESSAGE, "indent", C__LOW, C__OK);
                    } // if
                }
                else
                {
                    $l_log->add($g_absdir . " - No write permission", C__MESSAGE, "indent", C__HIGH, C__ERR);
                } // if
            }
            else
            {
                $l_log->add("No files to delete this time", C__MESSAGE, "indent", C__LOW, C__OK);
            } // if
        }
    }

    /**
     * Copy files
     *
     * @return bool
     */
    public function copy()
    {
        global $g_absdir;

        $this->m_path = str_replace('/', DS, rtrim($this->get_path(), '/')) . DS;;

        $l_absdir = rtrim(
            str_replace(
                [
                    '/',
                    '\\'
                ],
                DS,
                $g_absdir
            ),
            DS
        );
        $l_log    = isys_update_log::get_instance();

        /**
         * Copying module files first:
         */
        /*
        $l_prioPaths = array($this->m_path . 'src/classes/modules');

        foreach ($l_prioPaths as $l_prioPath)
        {
            $l_prioPath  = rtrim(str_replace(array('/', '\\'), DS, $l_prioPath), DS);
            $l_prioFiles = new DirectoryIterator($l_prioPath);

            foreach ($l_prioFiles as $l_prioFile)
            {
                $l_prioFile    = str_replace(array('/', '\\'), DS, $l_prioFile);
                $l_source_file = $l_prioPath . DS . $l_prioFile;

                if (strpos($l_prioFile, '.') !== 0)
                {
                    $l_dest_file = $l_absdir . DS . $l_prioFile;

                    if (!is_dir($l_source_file))
                    {
                        if (file_exists($l_source_file))
                        {
                            if (is_writeable($l_dest_file))
                            {
                                if (copy($l_source_file, $l_dest_file))
                                {
                                    $l_log->add('Copying priority file "' . $l_prioPath . DS . $l_prioFile . '"', C__MESSAGE, "indent", C__LOW, C__DONE);
                                }
                            }
                            else
                            {
                                $l_log->add('Error copying file "' . $l_prioPath . DS . $l_prioFile . '": Permission Denied', C__ERROR, "indent", C__HIGH, C__ERR);
                            }
                        }
                        else
                        {
                            if (copy($l_source_file, $l_dest_file))
                            {
                                $l_log->add('Copying priority file "' . $l_prioPath . DS . $l_prioFile . '"', C__MESSAGE, "indent", C__LOW, C__DONE);
                            }
                        }
                    }
                }
            }
        }
        */

        $l_filearray   = $this->getdir($this->m_path);
        $l_success     = true;
        $l_path_length = strlen($this->m_path);

        if (is_writeable($g_absdir))
        {
            if (count($l_filearray) > 0)
            {
                if (!isys_settings::get('system.devmode', false))
                {
                    $l_log->debug('Removing vendor directory (' . $l_absdir . '/vendor)');

                    // Remove vendor directory
                    $deleted = $undeleted = 0;
                    if (file_exists($l_absdir . 'vendor') && is_writeable($l_absdir . 'vendor'))
                    {
                        isys_glob_delete_recursive($l_absdir . 'vendor', $deleted, $undeleted);
                        if (!file_exists($l_absdir . 'vendor'))
                        {
                            mkdir(file_exists($l_absdir . 'vendor'));
                        }
                    }
                }

                // Copy files from ../files/ to the i-doit directory
                foreach ($l_filearray as $l_value)
                {
                    $l_value = substr(
                        str_replace(
                            [
                                '/',
                                '\\'
                            ],
                            DS,
                            $l_value
                        ),
                        $l_path_length
                    );

                    $l_source_file = $this->m_path . $l_value;

                    if (file_exists($l_source_file))
                    {
                        $l_dest_file = $l_absdir . DS . $l_value;
                        $l_this_dir  = dirname($l_dest_file);

                        if (!is_dir($l_this_dir))
                        {
                            if (mkdir($l_this_dir, 0777, true))
                            {
                                $l_log->debug('Creating directory "' . $l_this_dir . '"');
                            }
                            else
                            {
                                $l_log->debug('Could not create directory "' . $l_this_dir . '"');
                                $_SESSION['error']++;
                            } // if
                        } // if

                        // Physical copy.
                        if (!is_dir($l_source_file))
                        {
                            try
                            {
                                if ((file_exists($l_dest_file) && is_writeable($l_dest_file)) || is_writeable(dirname($l_dest_file)))
                                {
                                    if (copy($l_source_file, $l_dest_file))
                                    {
                                        $l_log->add('Copying ".' . DS . $l_value . '"', C__MESSAGE, "indent", C__LOW, C__DONE);
                                    }
                                    else
                                    {
                                        throw new Exception('Could not copy ' . $l_value);
                                    } // if
                                }
                                else
                                {
                                    throw new Exception('Could not create/update ' . $l_dest_file . ': Permission denied');
                                }
                            }
                            catch (Exception $e)
                            {
                                $l_log->add($e->getMessage(), C__ERROR, "indent", C__HIGH, C__ERR);
                                $l_log->debug('Copy command: copy("' . $l_source_file . '", "' . $l_dest_file . '");');

                                $_SESSION["error"]++;
                                $l_success = false;
                            }
                        }
                        else
                        {
                            if (!is_dir($l_dest_file))
                            {
                                mkdir($l_dest_file, 0777, true);
                            }
                        } // if
                    }
                } // foreach
            }
            else
            {
                $l_log->add('No files to update this time.', C__MESSAGE, "indent", C__LOW, C__DONE);
            }
        }
        else
        {
            $l_log->add("Failed.. " . $g_absdir . " not writeable for webserver user!", C__ERROR, "indent", C__HIGH, C__ERR);
            $l_log->add("- Check webserver writing permissions for the i-doit directory.", C__ERROR, "superindent");
        } // if

        return $l_success;
    } // function

    /**
     * Read directory and build an array with all updatable files
     *
     * @return  mixed  Will return an instance of "RecursiveIteratorIterator" if any files were found - null if not.
     */
    public function getdir()
    {
        $p_dir = str_replace('/', DS, rtrim($this->get_path(), '/')) . DS;

        if (!empty($p_dir) && $p_dir != DS)
        {
            if (is_dir($p_dir))
            {
                return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p_dir), RecursiveIteratorIterator::SELF_FIRST);
            } // if
        } // if

        return null;
    } // function

    /**
     * Reads a zip file and extracts it with "-extract_zip()".
     *
     * @param   string  $p_zipfile
     * @param   string  $p_dest
     * @param   boolean $p_single_file
     * @param   boolean $p_overwrite
     *
     * @return  array
     */
    public function read_zip($p_zipfile, $p_dest, $p_single_file = false, $p_overwrite = false)
    {
        $l_data = "";

        if (class_exists("ZipArchive"))
        {
            $l_zip = new ZipArchive();
            if ($l_zip->open($p_zipfile) === true)
            {
                $l_zip->extractTo($p_dest, $p_single_file ? $p_single_file : null);
                $l_zip->close();

                return true;
            }
            else return false;
        }

        if (!function_exists("file_get_contents"))
        {
            if (($l_fp = @fopen($p_zipfile, "rb")) !== false)
            {
                while (!feof($l_fp))
                {
                    $l_data .= fread($l_fp, 4096);
                }
                fclose($l_fp);
            }
        }
        else
        {
            $l_data = @file_get_contents($p_zipfile);
        }

        if ($l_data === false) return false;

        return $this->extract_zip($l_data, $p_dest, $p_single_file, $p_overwrite);
    }

    /**
     * Checks if a remote file exists
     *
     * @param array $p_url
     *
     * @return bool
     */
    public function remote_file_exists($p_url)
    {
        $l_url = parse_url($p_url);

        $l_tmp   = "";
        $l_fsock = fsockopen($l_url["host"], !isset($l_url["port"]) ? 80 : $l_url["port"], $l_tmp, $l_tmp, 8);
        if (!$l_fsock) return false;

        fputs($l_fsock, "HEAD " . $l_url["path"] . " HTTP/1.0\r\nHost:" . $l_url['host'] . "\r\n\r\n");
        $l_header = fread($l_fsock, 1024);
        fclose($l_fsock);

        return preg_match("~^HTTP/.+\s+200~i", $l_header);
    }

    /**
     * Extracts a zip file and returns an array with the extracted files.
     * Based on the zlip wrapper of SMF: Simple Machines Forum http://www.simplemachines.org
     *
     * @param   string  $p_data
     * @param   string  $p_destination
     * @param   boolean $p_single_file
     * @param   boolean $p_overwrite
     *
     * @return  array
     */
    private function extract_zip($p_data, $p_destination, $p_single_file = false, $p_overwrite = false)
    {
        umask(0);
        if ($p_destination !== null && !file_exists($p_destination) && !$p_single_file)
        {
            mkdir($p_destination, 0777, true);
        } // if

        // Check for zip headerinfo.
        if (substr($p_data, 0, 2) != "PK")
        {
            return false;
        } // if

        if (substr($p_data, -22, 4) == "PK" . chr(5) . chr(6))
        {
            $l_p = -22;
        }
        else
        {
            for ($l_p = -22;$l_p > -strlen($p_data);$l_p--)
            {
                if (substr($p_data, $l_p, 4) == "PK" . chr(5) . chr(6))
                {
                    break;
                } // if
            } // for
        } // if

        $l_extracted = [];

        // Get the basic zip file info.
        $l_zip_info = unpack("vfiles/Vsize/Voffset", substr($p_data, $l_p + 10, 10));

        $l_p = $l_zip_info["offset"];
        for ($i = 0;$i < $l_zip_info["files"];$i++)
        {
            // Make sure this is a file entry...
            if (substr($p_data, $l_p, 4) != "PK" . chr(1) . chr(2))
            {
                return false;
            }

            // Get all the important file information.
            $l_file_info             = unpack(
                "Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset",
                substr($p_data, $l_p + 16, 30)
            );
            $l_file_info["filename"] = substr($p_data, $l_p + 46, $l_file_info["filename_len"]);

            // Skip all the information we don"t care about anyway.
            $l_p += 46 + $l_file_info["filename_len"] + $l_file_info["extra_len"] + $l_file_info["comment_len"];

            // If this is a file, and it doesn"t exist.... happy days!
            if (substr($l_file_info["filename"], -1, 1) != "/" && !file_exists($p_destination . "/" . $l_file_info["filename"]))
            {
                $l_write = true;
            }
            // If the file exists, we may not want to overwrite it.
            elseif (substr($l_file_info["filename"], -1, 1) != "/")
            {
                $l_write = $p_overwrite;
            } // This is a directory, so we"re gonna want to create it. (probably...)
            elseif ($p_destination !== null && !$p_single_file)
            {
                // Just a little accident prevention, don"t mind me.
                $l_file_info["filename"] = strtr(
                    $l_file_info["filename"],
                    [
                        "../" => "",
                        "/.." => ""
                    ]
                );

                if (!file_exists($p_destination . "/" . $l_file_info["filename"]))
                {
                    mkdir($p_destination . "/" . $l_file_info["filename"], 0777);
                }
                $l_write = false;
            }
            else
            {
                $l_write = false;
            }

            // Okay!  We can write this file, looks good from here...
            if ($l_write && $p_destination !== null)
            {
                if (strpos($l_file_info["filename"], "/") !== false && !$p_single_file)
                {
                    // Make any parents this file may need to have for things to work out.
                    $l_dirs = explode("/", $l_file_info["filename"]);
                    array_pop($l_dirs);

                    $l_dirpath = $p_destination . "/";
                    foreach ($l_dirs as $l_dir)
                    {
                        if (!file_exists($l_dirpath . $l_dir)) mkdir($l_dirpath . $l_dir, 0777);
                        $l_dirpath .= $l_dir . "/";
                    }
                }

                // Check that the data exists
                if (substr($p_data, $l_file_info["offset"], 4) == "PK" . chr(3) . chr(4))
                {

                    // Get the actual compressed data.
                    $l_file_info["data"] = substr(
                        $p_data,
                        $l_file_info["offset"] + 30 + $l_file_info["filename_len"] + $l_file_info["extra_len"],
                        $l_file_info["compressed_size"]
                    );

                    // Only inflate it if we need to
                    if ($l_file_info["compressed_size"] != $l_file_info["size"])
                    {
                        $l_file_info["data"] = @gzinflate($l_file_info["data"]);
                    }

                    if ($p_single_file && ($p_destination == $l_file_info["filename"] || $p_destination == "*/" . basename($l_file_info["filename"])))
                    {

                        return $l_file_info["data"];

                    }
                    elseif ($p_single_file)
                    {
                        continue;
                    }

                    file_put_contents($p_destination . "/" . $l_file_info["filename"], $l_file_info["data"]);
                }
                else
                {
                    return false;
                }
            }

            if (substr($l_file_info["filename"], -1, 1) != "/")
            {
                $l_extracted[] = [
                    "filename" => $l_file_info["filename"],
                    "size"     => $l_file_info["size"],
                    "skipped"  => false
                ];
            }
        }

        if ($p_single_file)
        {
            return false;
        }
        else
        {
            return $l_extracted;
        }
    }

    /**
     * isys_update_files constructor.
     *
     * @param null $p_path
     */
    public function __construct($p_path = null)
    {
        $this->init($p_path);
    } // function
} // class