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
 * "Monitoring" Module language file
 *
 * @package        monitoring
 * @subpackage     Language
 * @author         Leonard Fischer <lfischer@i-doit.com>
 * @copyright      2013 synetics GmbH
 * @version        1.0.0
 * @license        http://www.i-doit.com/license
 */

return [
    'LC__CATG__MONITORING__INSTANCE'                                       => 'Monitoring Instanz',
    'LC__CATG__MONITORING__ACTIVE'                                         => 'Aktiv?',
    'LC__CATG__MONITORING__ALIAS'                                          => 'Alias',
    'LC__CATG__MONITORING__HOSTNAME'                                       => 'Hostname',
    'LC__CATG__MONITORING__HOSTNAME_SELECTION'                             => 'Hostname auswahl',
    'LC__CATG__MONITORING__HOST'                                           => 'Host',
    'LC__CATG__LIVESTATUS'                                                 => 'Livestatus',
    'LC__CATG__LIVESTATUS__CURRENT_STATE'                                  => 'Aktueller status',
    'LC__CATG__LIVESTATUS__HOST_STATE'                                     => 'Host status',
    'LC__CATG__LIVESTATUS__SERVICE_STATE'                                  => 'Service status',
    'LC__CATG__LIVESTATUS__NO_DATA'                                        => 'Es wurden keine Daten von Livestatus empfangen.',
    'LC__CATG__NDO'                                                        => 'NDO',
    'LC__CATG__NDO__STATUS_CGI'                                            => 'Status CGI',
    'LC__MONITORING'                                                       => 'Monitoring',
    'LC__MONITORING__LIVESTATUS'                                           => 'Livestatus',
    'LC__MONITORING__LIVESTATUS_STATUS'                                    => 'Livestatus',
    'LC__MONITORING__LIVESTATUS_STATUS_BUTTON'                             => 'Livestatus (Knopf)',
    'LC__MONITORING__NDO'                                                  => 'NDO',
    'LC__MONITORING__LIVESTATUS_NDO__CONFIGURATION'                        => 'Livestatus/NDO',
    'LC__MONITORING__LIVESTATUS_NDO__CONFIGURATION_EDIT'                   => 'Livestatus/NDO Konfiguration bearbeiten',
    'LC__MONITORING__EXPORT__CONFIGURATION'                                => 'Exportkonfiguration',
    'LC__MONITORING__EXPORT__CONFIGURATION_EDIT'                           => 'Exportkonfiguration bearbeiten',
    'LC__MONITORING__EXPORT__CONFIGURATION_OPTIONS'                        => 'Optionen für Exportkonfiguration',

    // Configuration labels.
    'LC__MONITORING__EXPORT__PATH'                                         => 'Export Verzeichnis',
    'LC__MONITORING__ACTIVE'                                               => 'Aktiv?',
    'LC__MONITORING__TYPE'                                                 => 'Monitoring Typ',
    'LC__MONITORING__CONNECTION'                                           => 'Verbindungsart',
    'LC__MONITORING__PATH'                                                 => 'Pfad',
    'LC__MONITORING__ADDRESS'                                              => 'Adresse',
    'LC__MONITORING__PORT'                                                 => 'Port',
    'LC__MONITORING__DBNAME'                                               => 'Datenbankname / Schema',
    'LC__MONITORING__DBPREFIX'                                             => 'DB Prefix',
    'LC__MONITORING__USERNAME'                                             => 'Benutzer',
    'LC__MONITORING__PASSWORD'                                             => 'Passwort',
    'LC__MONITORING__MONITORING_ADDRESS'                                   => 'Link zum Monitoring Tool',
    'LC__MONITORING__MONITORING_ADDRESS_INFO'                              => 'Achtung: Nagios benötigt den String "/status.cgi?host=" am ende',
    'LC__MONITORING__MONITORING_TYPE'                                      => 'Typ',
    'LC__MONITORING__CHECK_MK__SITE'                                       => 'Site',
    'LC__MONITORING__CHECK_MK__ROLE_EXPORT'                                => 'Zugewiesene Kontakte exportieren',
    'LC__MONITORING__CHECK_MK__ROLE_EXPORT_DESCRIPTION'                    => 'Zugewiesene Kontakte benötigen die folgende Rolle um im Export zu erscheinen',
    'LC__MONITORING__CHECK_MK__MULTISITE'                                  => 'Multisite',
    'LC__MONITORING__CHECK_MK__MULTISITE_INFO'                             => 'Distributed Monitoring',
    'LC__MONITORING__CHECK_MK__MASTER_SITE'                                => 'Master Site',
    'LC__MONITORING__CHECK_MK__MASTER_SITE_OF'                             => 'Master Site von',
    'LC__MONITORING__CHECK_MK__LOCK_HOSTS'                                 => 'Hosts sperren',
    'LC__MONITORING__CHECK_MK__LOCK_FOLDERS'                               => 'Ordner sperren',
    'LC__MONITORING__EXPORT_PATH'                                          => 'Lokaler Pfad',
    'LC__MONITORING__EXPORT_PATH_WARNING'                                  => 'Achtung: das Verzeichnis muss absolut angegeben werden. Außerdem werden dessen Inhalte vom System vor jedem Export geleert!',
    'LC__MONITORING__NDO__STATUS'                                          => 'NDO Status',
    'LC__MONITORING__NDO__STATUS_BUTTON'                                   => 'NDO Status Button',

    // Exception translations.
    'LC__MONITORING__LIVESTATUS_EXCEPTION__NO_CONFIG'                      => 'Bitte definieren Sie mindestens einen Monitoring Host.',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__PHP_EXTENSION_MISSING'          => 'Es scheint, als würde die "sockets" PHP Extension fehlen!',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CREATE_SOCKET'        => 'Es konnte kein Socket zum Livestatus erstellt werden.',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CONNECT_LIVESTATUS'   => 'Es konnte keine Verbindung zum Livestatus hergestellt werden.',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET'     => 'Beim lesen der Daten ist ein Fehler aufgetreten: %s',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__INVALID_FORMAT'                 => 'Das Abfrage-Ergebnis hat ein unbekanntes Format!',
    'LC__MONITORING__NDO_EXCEPTION__NO_CONFIG'                             => 'Bitte definieren Sie mindestens einen NDO Host.',

    // Widget translations
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS'                                 => 'Monitoring: Gefährdete Hosts',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__HOST_SELECTION'                 => 'Hostauswahl',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__SERVICE'                        => 'Service',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__NO_SERVICES'                    => 'Services können bei dieser Monitoring Instanz nicht abgefragt werden.',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__UNSUPPORTED_HOST'               => 'Der ausgewählte Monitoring Host wird nicht unterstützt!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__PLEASE_SELECT_HOST'             => 'Sie müssen zunächst einen (aktiven) Monitoring Host auswählen!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__ALL_HOSTS_OK'                   => 'Es konnten keine ausgefallenen Hosts gefunden werden!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__FOUND_HOSTS_THAT_ARE_NOT_OK'    => 'Achtung! Es wurden Hosts gefunden deren Status nicht "OK" lautet:',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__ALL_SERVICES_OK'                => 'Es konnten keine ausgefallenen Services gefunden werden!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__FOUND_SERVICES_THAT_ARE_NOT_OK' => 'Achtung! Es wurden Services gefunden deren Status nicht "OK" lautet:',
];