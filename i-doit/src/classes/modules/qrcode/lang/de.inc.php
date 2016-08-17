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
    'LC__MODULE__QRCODE__CONFIGURATION'                          => 'Globale QR-Code Konfiguration',
    'LC__MODULE__QRCODE__CONFIGURATION__BY_OBJ_TYPE'             => 'QR-Code Konfiguration nach Objekt-Typ',
    'LC__MODULE__QRCODE__CONFIGURATION__ENABLE'                  => 'QR-Codes für diesen Objekt-Typen darstellen?',
    'LC__MODULE__QRCODE__CONFIGURATION__QR_METHOD'               => 'Methode zur Generierung des QR-Codes',
    'LC__MODULE__QRCODE__CONFIGURATION__QR_LINK'                 => 'QR-Code Verlinkung',
    'LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_ENABLE'           => 'Die QR-Codes lassen sich auf Wunsch auch komplett deaktiveren. Navigieren Sie hierzu in die "Systemeinstellungen > Mandanteneinstellungen".',
    'LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_CONFIGURATION'    => 'Die globale Konfiguration bezieht sich auf alle Objekte und Objekt-Typen, die nicht explizit eigene Konfigurationen mitbringen.',
    'LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL'             => 'Primäre Zugriffs-URL',
    'LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL_DESCRIPTION' => 'QR-Code wird anhand der Primären Zugriffs-URL aus der Kategorie Zugriff generiert',
    'LC__MODULE__QRCODE__CONFIGURATION__DESCRIPTION'             => 'Text Template',
    'LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_DEFINITION'       => 'Globale Definition',
    'LC__MODULE__QRCODE__CONFIGURATION__LINK_IQR'                => 'Aufruf des i-doit QR-Code Printer Tools (IQR Link)',
    'LC__MODULE__QRCODE__CONFIGURATION__LINK_PRINT'              => 'Popup mit Druckfunktion',
    'LC__MODULE__QRCODE__CONFIGURATION__LOGO'                    => 'Logo für Druckansicht',
    'LC__MODULE__QRCODE__CONFIGURATION__NEW_CONFIG'              => 'Neue Konfiguration',
    'LC__MODULE__QRCODE__CONFIGURATION__NO_OBJTYPE_CONFIG'       => 'Aktuell verfügt kein Objekt-Typ über eine Konfiguration',

    // Translations for the auth GUI.
    'LC__AUTH_GUI__BLABLA'                                       => 'Tags konfigurieren',
    'LC__AUTH__QRCODE_EXCEPTION__BLABLA'                         => 'Es ist Ihnen nicht erlaubt, die QRCODE Tag konfiguration öffnen.',

    // Translations for the report view.
    'LC__REPORT__VIEW__QR_CODES'                                 => 'QR Codes',
    'LC__REPORT__VIEW__QR_CODES_DESCRIPTION'                     => 'Zeigt mehrere QR Codes von mehreren Objekten in einer Druckbaren Ansicht an.',
    'LC__REPORT__VIEW__QR_CODES_SELECT_OBJECTS'                  => 'Bitte wählen Sie die Objekte aus, deren QR Code Sie dargestellt bekommen möchten.',
    'LC__REPORT__VIEW__QR_CODES_SIZE'                            => 'Größe des QR Codes',
    'LC__REPORT__VIEW__QR_CODES_COLUMNS'                         => 'Anzahl der Spalten',
    'LC__REPORT__VIEW__QR_CODES_POPUP'                           => 'Ergebnis im Popup öffnen',
    'LC__REPORT__VIEW__QR_CODES__NO_URL_MESSAGE'                 => 'Achtung! Für das Objekt %s konnte keine URL generiert werden.',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION'                     => 'Fehlerkorrektur',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__LOW'                => 'Niedrig',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__MEDIUM'             => 'Normal',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__QUALITY'            => 'Gut',
    'LC__REPORT__VIEW__QR_CODES__CORRECTION__HIGH'               => 'Hoch',
    'LC__REPORT__VIEW__QR_CODES__CONFIGURATION_LINK'             => 'Zur QR-Code Konfiguration',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT'                         => 'Textausrichtung',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_LEFT'                    => 'Linksbündig',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_CENTER'                  => 'Mittig',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_RIGHT'                   => 'Rechtsbündig',
    'LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_JUSTIFY'                 => 'Blocksatz',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_SELECTION'               => 'Layout auswählen',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_QRCODE'                  => 'QR Code',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_DESCRIPTION'             => 'Text Template',
    'LC__REPORT__VIEW__QR_CODES__LAYOUT_LOGO'                    => 'Logo'
];