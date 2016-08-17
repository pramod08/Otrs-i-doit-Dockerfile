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
 * Helper methods for Crypting
 * Extension mcrypt must be installed/loaded
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_helper_crypt
{
    /**
     * Crypt cipher
     *
     * @var string
     */
    private static $m_crypt_cipher = MCRYPT_RIJNDAEL_128;

    /**
     * Crypt mode
     *
     * @var string
     */
    private static $m_crypt_mode = MCRYPT_MODE_NOFB;

    /**
     * Encrypts the string
     *
     * @static
     *
     * @param $p_string        String
     * @param $p_crypt_key     String|null
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function encrypt($p_string, $p_crypt_key = null)
    {
        if (!function_exists('mcrypt_module_open')) return $p_string;

        if (!empty($p_string))
        {
            $l_crypt_key = ($p_crypt_key !== null) ? $p_crypt_key : C__CRYPT_KEY;
            if (strlen($l_crypt_key))
            {
                $l_resource = self::open_crypt_module();
                // Create random vector for encryption
                $l_iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($l_resource), MCRYPT_RAND);
                // Initializes all buffers needed for encryption
                mcrypt_generic_init($l_resource, $l_crypt_key, $l_iv);
                // Encrypts string
                $l_crypted_string = $l_iv . '|$$|' . mcrypt_generic($l_resource, $p_string);
                self::close_crypt_module($l_resource);

                return base64_encode($l_crypted_string);
            } // if
        } // if
        return $p_string;
    }

    /**
     * Decrypts the string
     *
     * @static
     *
     * @param $p_string       String
     * @param $p_crypt_key    String|null
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function decrypt($p_string, $p_crypt_key = null)
    {
        if (!function_exists('mcrypt_module_open')) return $p_string;

        if (!empty($p_string))
        {
            $l_crypt_key = ($p_crypt_key !== null) ? $p_crypt_key : C__CRYPT_KEY;
            if (strlen($l_crypt_key) && ($l_crypted_string = base64_decode($p_string, true)))
            {
                // Check if string has been really encrypted
                if (strpos($l_crypted_string, '|$$|') === false) return $p_string;
                // Decodes and splits the vector with the encrypted string
                $l_crypted_array = explode('|$$|', $l_crypted_string);
                $l_resource      = self::open_crypt_module();
                // Initializes all buffers needed for decryption
                mcrypt_generic_init($l_resource, $l_crypt_key, $l_crypted_array[0]);
                // Decrypts string
                $l_decrypted_string = mdecrypt_generic($l_resource, $l_crypted_array[1]);
                self::close_crypt_module($l_resource);

                return $l_decrypted_string;
            } // if
        } // if
        return $p_string;
    }

    /**
     * Wrapper for mcrypt_module_open
     *
     * @static
     * @return resource
     */
    private static function open_crypt_module()
    {
        return mcrypt_module_open(self::$m_crypt_cipher, '', self::$m_crypt_mode, '');
    }

    /**
     * Wrapper for mcrypt_module_close
     *
     * @static
     *
     * @param $p_resource
     *
     * @return bool
     */
    private static function close_crypt_module($p_resource)
    {
        return mcrypt_module_close($p_resource);
    }

} // class
?>