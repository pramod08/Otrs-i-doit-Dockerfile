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
 * SOAP Wrapper.
 *
 * @package     i-doit
 * @subpackage  Libraries
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define("SOAP_DIR", $g_dirs["class"] . "libraries/nusoap/");

if (include_once(SOAP_DIR . "nusoap.php"))
{
    class isys_library_soap
    {
        /**
         * Static method for returning an instance of "nusoap_client".
         *
         * @param   string  $p_url
         * @param   boolean $p_useWSDL
         *
         * @return  nusoap_client
         */
        public static function client($p_url, $p_useWSDL = false)
        {
            if (!isys_settings::get('proxy.active', false))
            {
                return new nusoap_client($p_url, $p_useWSDL);
            }
            else
            {
                return new nusoap_client(
                    $p_url,
                    $p_useWSDL,
                    isys_settings::get('proxy.host'),
                    isys_settings::get('proxy.port'),
                    isys_settings::get('proxy.username'),
                    isys_settings::get('proxy.password')
                );
            } // if
        } // function

        /**
         * Static method for returning an instance of "soap_server".
         *
         * @param   boolean $p_useWSDL
         *
         * @return  nusoap_server
         */
        public static function server($p_useWSDL = false)
        {
            return new soap_server($p_useWSDL);
        } // function
    } // class
} // if