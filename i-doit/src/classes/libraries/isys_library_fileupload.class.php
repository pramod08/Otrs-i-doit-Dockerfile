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
 * FileUpload wrapper
 * Implements the qqFileUploader API for uploading files.
 * Is licensed under MIT, GPL 2 and LGPL.
 *
 * @package     i-doit
 * @subpackage  Libraries
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

include_once("fileupload/file_upload.php");

class isys_library_fileupload extends qqFileUploader
{
    /**
     * This is used for setting an own name.
     *
     * @var  string
     */
    private $m_prefix = null;

    /**
     * Method for setting a customa filename. This method may break future updates!!
     *
     * @param   string $p_prefix
     *
     * @return  qqFileUploader
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_prefix($p_prefix)
    {
        $this->m_prefix = $p_prefix;

        return $this;
    } // function

    /**
     * Overwritten method from qqFileUploader, for usage of "$this->prefix".
     *
     * @param   string  $p_upload_dir
     * @param   boolean $p_replace_old_file
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    function handleUpload($p_upload_dir, $p_replace_old_file = true)
    {
        if (!is_writable($p_upload_dir))
        {
            return ['error' => _L('LC__UNIVERSAL__FILE_UPLOAD__NO_WRITE_PERMISSIONS')];
        } // if

        if (!$this->file)
        {
            return ['error' => _L('LC__UNIVERSAL__FILE_UPLOAD__NO_FILE_SELECTED')];
        } // if

        $size = $this->file->getSize();

        if ($size == 0)
        {
            return ['error' => _L('LC__UNIVERSAL__FILE_UPLOAD__NO_FILE_SELECTED')];
        } // if

        if ($size > $this->sizeLimit)
        {
            return ['error' => _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_SIZE_TOO_BIG')];
        } // if

        $pathinfo = pathinfo($this->file->getName());

        $filename = $pathinfo['filename'];
        $ext      = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions))
        {
            $these = implode(', ', $this->allowedExtensions);

            return ['error' => _L('LC__UNIVERSAL__FILE_UPLOAD__EXTENSION_ERROR') . ' - ' . $these];
        } // if

        $ext = ($ext == '') ? $ext : '.' . $ext;

        if (!$p_replace_old_file)
        {
            while (file_exists($p_upload_dir . $filename . $ext))
            {
                $filename .= rand(10, 99);
            } // while
        } // if

        $this->uploadName = $filename = $filename . $ext;

        if ($this->m_prefix !== null)
        {
            $this->uploadName = $filename = $this->m_prefix . $this->uploadName;
        } // if

        if ($this->file->save($p_upload_dir . $filename))
        {
            return ['success' => true];
        }
        else
        {
            return [
                'error' => _L('LC__UNIVERSAL__FILE_UPLOAD__FILE_UPLOADED_PARTIALLY')
            ];
        } // if
    } // function

    /**
     * qqFileUpload constructor, for automatically setting the size-limit to the values, defined in php.ini.
     *
     * @param   array   $p_allowed_extensions
     * @param   integer $p_size_limit
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(array $p_allowed_extensions = [], $p_size_limit = null)
    {
        if ($p_size_limit === null)
        {
            // For setting the size limit, we read the ini-configurations.
            $postSize   = $this->toBytes(ini_get('post_max_size'));
            $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

            // Choose the smaller value for "size limitation".
            if ($postSize > $uploadSize)
            {
                $p_size_limit = $uploadSize;
            }
            else
            {
                $p_size_limit = $postSize;
            } // if
        } // if

        parent::__construct($p_allowed_extensions, $p_size_limit);
    } // function
} // class