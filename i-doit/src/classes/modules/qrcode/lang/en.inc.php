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
 * "QR-Code" Module language file
 *
 * @package        qrcode
 * @subpackage     Language
 * @author         Leonard Fischer <lfischer@i-doit.com>
 * @copyright      2013 synetics GmbH
 * @version        1.0.0
 * @license        http://www.i-doit.com/license
 */

return [
    'LC__MODULE__QRCODE'                                         => 'QR-Code',
    'LC__MODULE__QRCODE__CONFIGURATION'                          => 'Global QR-Code configuration',
    'LC__MODULE__QRCODE__CONFIGURATION__BY_OBJ_TYPE'             => 'QR-Code configuration for each object type',
    'LC__MODULE__QRCODE__CONFIGURATION__ENABLE'                  => 'Display QR-Codes for this object type?',
    'LC__MODULE__QRCODE__CONFIGURATION__QR_METHOD'               => 'Method for generating the QR-Codes',
    'LC__MODULE__QRCODE__CONFIGURATION__QR_LINK'                 => 'QR-Code link',
    'LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_ENABLE'           => 'QR-Codes can be deactivated completely. Navigate to "System settings > Tenantsettings" to do so.',
    'LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_CONFIGURATION'    => 'The globale configuration affects all objects and object types, which do not have an own explicit configuration.',
    'LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL'             => 'Primary access URL',
    'LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL_DESCRIPTION' => 'QR-Code will be generated via the primary access URL',
    'LC__MODULE__QRCODE__CONFIGURATION__DESCRIPTION'             => 'Text template',
    'LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_DEFINITION'       => 'Global definition',
    'LC__MODULE__QRCODE__CONFIGURATION__LINK_IQR'                => 'Calls the i-doit QR-Code printer tool (IQR Link)',
    'LC__MODULE__QRCODE__CONFIGURATION__LINK_PRINT'              => 'Popup with print function',
    'LC__MODULE__QRCODE__CONFIGURATION__LOGO'                    => 'Logo for print-view',
    'LC__MODULE__QRCODE__CONFIGURATION__NEW_CONFIG'              => 'New configuration',
    'LC__MODULE__QRCODE__CONFIGURATION__NO_OBJTYPE_CONFIG'       => 'Currently there is no object type which inherits its own configuration',
    // Translations for the report view.
    'LC__REPORT__VIEW__QR_CODES'                                 => 'QR Codes',
    'LC__REPORT__VIEW__QR_CODES_DESCRIPTION'                     => 'Displays multiple QR Codes of selected objects in a printable view.',
    'LC__REPORT__VIEW__QR_CODES_SELECT_OBJECTS'                  => 'Please select some objects, of which you want to display the QR Codes',
    'LC__REPORT__VIEW__QR_CODES_SIZE'                            => 'Size of the QR Code',
    'LC__REPORT__VIEW__QR_CODES_COLUMNS'                         => 'Amount of columns',
    'LC__REPORT__VIEW__QR_CODES_POPUP'                           => 'Open the result inside a popup',
    'LC__REPORT__VIEW__QR_CODES__NO_URL_MESSAGE'                 => 'Attention! The URL for the object %s could not be generated.',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION'                     => 'Error correction',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__LOW'                => 'Low',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__MEDIUM'             => 'Medium',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__QUALITY'            => 'Good',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__HIGH'               => 'High',
    'LC__REPORT__VIEW__QR_CODES__CONFIGURATION_LINK'             => 'Open the QR-Code configuration',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT'                         => 'Text alignment',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_LEFT'                    => 'Left',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_CENTER'                  => 'Center',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_RIGHT'                   => 'Right',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_JUSTIFY'                 => 'Justify',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_SELECTION'               => 'Select a layout',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_QRCODE'                  => 'QR Code',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_DESCRIPTION'             => 'Text template',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_LOGO'                    => 'Logo'
];