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
 * Wrapper for ZipArchive
 *
 * @package     i-doit
 * @subpackage  Libraries
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_library_zip extends ZipArchive
{
    /**
     * Gets the localized status error message, system and/or zip messages.
     *
     * @link    http://php.net/manual/en/ziparchive.getstatusstring.php
     * @return  string  A string with the status message on success or false on failure.
     */
    public function getStatusString()
    {
        return _L(parent::getStatusString());
    } //function
} //class