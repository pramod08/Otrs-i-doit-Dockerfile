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
 * "Check_MK" Module language file.
 *
 * @package        Modules
 * @subpackage     Check_MK
 * @author         Leonard Fischer <lfischer@i-doit.com>
 * @copyright      2013 synetics GmbH
 * @version        1.0.0
 * @license        http://www.i-doit.com/license
 */

return [
    'LC__MODULE__CHECK_MK'                                                     => 'Check_MK',
    'LC__MODULE__CHECK_MK__CONFIGURATION'                                      => 'Konfiguration',
    'LC__MODULE__CHECK_MK__EXPORT'                                             => 'Check_MK Export',
    'LC__MODULE__CHECK_MK__EXPORT__PATH'                                       => 'Export Verzeichnis',
    'LC__MODULE__CHECK_MK__START_EXPORT'                                       => 'Export starten',
    'LC__MODULE__CHECK_MK__START_SHELLSCRIPT'                                  => 'Export starten und Transfer Shellskript ausführen',
    'LC__MODULE__CHECK_MK__START_SHELLSCRIPT_DESCRIPTION'                      => 'Das "Transfer Shellskript" liegt im i-doit Hauptverzeichnis und kann von Ihnen angepasst werden um die exportierten Konfigurationsdateien automatisch auf den definierten Check_MK Host zu bringen.',
    'LC__MODULE__CHECK_MK__WAITING'                                            => 'Bereit zum Exportieren!',
    'LC__MODULE__CHECK_MK__EXPORTED_FILES'                                     => 'Exportierte Konfigurationen:',
    'LC__MODULE__CHECK_MK__CMK_INSTANCE'                                       => 'Check_MK Instanz',
    'LC__MODULE__CHECK_MK__HOST'                                               => 'Host',
    'LC__MODULE__CHECK_MK__HOSTS'                                              => 'Hosts',
    'LC__MODULE__CHECK_MK__HOSTS__NEW_HOST'                                    => 'Neuen Host anlegen',
    'LC__MODULE__CHECK_MK__LIVESTATUS'                                         => 'Livestatus',
    'LC__MODULE__CHECK_MK__LIVESTATUS__ACTIVE'                                 => 'Aktiv',
    'LC__MODULE__CHECK_MK__LIVESTATUS__TYPE'                                   => 'Typ',
    'LC__MODULE__CHECK_MK__LIVESTATUS__PATH'                                   => 'Pfad',
    'LC__MODULE__CHECK_MK__LIVESTATUS__ADDRESS'                                => 'Adresse',
    'LC__MODULE__CHECK_MK__LIVESTATUS__PORT'                                   => 'Port',
    'LC__MODULE__CHECK_MK__TAGS__NO_TAGS'                                      => 'Es wurden noch keine Merkmale eingepflegt',
    'LC__MODULE__CHECK_MK__EXPORT_STRUCTURE'                                   => 'Export Struktur',
    'LC__MODULE__CHECK_MK__EXPORT_LANGUAGE'                                    => 'Export Sprache',
    'LC__MODULE__CHECK_MK__EXPORT_LANGUAGE_ALL_AVAILABLE'                      => 'Alle verfügbaren',
    'LC__MODULE__CHECK_MK__EXPORT_WITHOUT_STRUCTURE'                           => 'Keine (alle Dateien in erster Ebene)',
    'LC__MODULE__CHECK_MK__EXPORT_IN_LOCATION_PATH'                            => 'Verzeichnisse nach physikalischem Standort',
    'LC__MODULE__CHECK_MK__EXPORT_IN_LOG_LOCATION_PATH'                        => 'Verzeichnisse nach logischem Standort',
    'LC__MODULE__CHECK_MK__EXPORT_OBJECT_TYPES'                                => 'Verzeichnisse nach Objekttypen',
    'LC__MODULE__CHECK_MK__EXPORT_PATH_WARNING'                                => 'Achtung: Die definierten Export-Verzeichnisse %s und deren Inhalte werden vor dem Export gelöscht!',
    // Translations for the Auth GUI.
    'LC__AUTH_GUI__CONFIGURATION'                                              => 'Check_MK konfiguration',
    'LC__AUTH_GUI__EXPORT'                                                     => 'Konfiguration exportieren',
    'LC__AUTH_GUI__TAG_CONFIG'                                                 => 'Merkmale konfigurieren',
    'LC__AUTH__CHECK_MK_EXCEPTION__MISSING_RIGHT_FOR_CONFIGURATION'            => 'Es ist Ihnen nicht erlaubt, die Check_MK konfiguration zu öffnen.',
    'LC__AUTH__CHECK_MK_EXCEPTION__MISSING_RIGHT_FOR_EXPORT'                   => 'Es ist Ihnen nicht erlaubt, die Check_MK konfiguration zu exportieren.',
    'LC__AUTH__CHECK_MK_EXCEPTION__MISSING_RIGHT_FOR_TAGS'                     => 'Es ist Ihnen nicht erlaubt, die Hostmerkmal-konfiguration öffnen.',
    // Adding some translations for the category.
    'LC__CATG__CMK'                                                            => 'Check_MK',
    'LC__CATG__CMK_DEF'                                                        => 'Export Parameter',
    'LC__CATG__CMK_FOLDER'                                                     => 'Check_MK (Host)',
    'LC__CATG__CMK_TAG'                                                        => 'Hostmerkmale',
    'LC__CATG__CMK_TAG_DYNAMIC'                                                => 'Hostmerkmale (dynamisch)',
    'LC__CATG__CMK__ACTIVE'                                                    => 'Aktiv',
    'LC__CATG__CMK__ALIAS'                                                     => 'Alias',
    'LC__CATG__CMK__HOSTNAME'                                                  => 'Hostname',
    'LC__CATG__CMK__EXPORT_IP'                                                 => 'IP-Adresse exportieren',
    'LC__CATG__CMK_TAG__ADD_TAG'                                               => 'Merkmal hinzufügen',
    'LC__CATG__CMK_TAG__TAGS'                                                  => 'Hostmerkmale',
    'LC__CATG__CMK_TAG__CMDB_TAGS'                                             => 'CMDB Merkmale',
    'LC__CATG__CMK_TAG__NO_CMDB_TAGS'                                          => 'Keine CMDB Merkmale',
    'LC__CATG__CMK_TAG__DYNAMIC_TAGS'                                          => 'Dynamische Merkmale',
    'LC__CATG__CMK_TAG__NO_DYNAMIC_TAGS'                                       => 'Keine dynamischen Merkmale',
    'LC__CATG__CMK_HOST_SERVICE'                                               => 'Servicezuweisung',
    'LC__CATG__CMK_SERVICE'                                                    => 'Check_MK Servicezuweisung',
    'LC__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT'                               => 'Softwarezuweisung',
    'LC__CATG__CMK_SERVICE__INHERITED_SERVICES'                                => 'Geerbte services via Softwarezuweisung',
    'LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES'                                 => 'Abhängig von',
    'LC__CATG__CMK_SERVICE__NO_SERVICES'                                       => 'Es konnten keine Services gefunden werden!',
    'LC__CATG__CMK_SERVICE_FOLDER'                                             => 'Check_MK (Service)',
    'LC__CATG__CHECK_MK_FOLDER'                                                => 'Check_MK',
    // Translations for a few dynamic conditions.
    'LC__MODULE__CHECK_MK__TAG_GROUP__AGENT_TYPE'                              => 'Agent type',
    'LC__MODULE__CHECK_MK__TAGS__CONDITION__NEW_OBJECTS_OF_TYPE'               => 'Objekte vom Typ',
    'LC__MODULE__CHECK_MK__TAGS__CONDITION__LOCATION'                          => 'Objekte im Standort',
    'LC__MODULE__CHECK_MK__TAGS__CONDITION__PURPOSE'                           => 'Objekte mit Einsatzzweck',
    'LC__MODULE__CHECK_MK__TAGS__ADD_DYNAMIC_TAG'                              => 'Dynamisches Merkmal hinzufügen',
    'LC__MODULE__CHECK_MK__TAGS__DYNAMIC_TAG'                                  => 'Dynamische Merkmale',
    'LC__MODULE__CHECK_MK__TAGS__DYNAMIC_TAG_ASSIGNMENT'                       => 'Dynamische Merkmale zuweisen',
    'LC__MODULE__CHECK_MK__TAGS__CMDB_TAG_CONFIG'                              => 'CMDB Merkmale konfigurieren',
    'LC__MODULE__CHECK_MK__TAGS__STATIC_TAG_CONFIG'                            => 'Hostmerkmale (statisch)',
    // Some CMDB GUI specific translations.
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_DESCRIPTION'                       => 'CMDB Merkmale werden mit Hilfe einfacher Regeln definiert und anschließend in der "Hostmerkmale" Kategorie und dem Konfigurations-export dargestellt. CMDB Merkmale können nicht händisch erstellt oder geändert werden, das i-doit System wird diese Aufgabe übernehmen.<br />Die CMDB Merkmale basieren auf bereits eingegebenen Attributen (wie z.B. einem Objekt-Typ Titel, einem Verwendungszweck, usw.) - die einzelnen Attribute werden lediglich von i-doit umkonvertiert, damit Sie dem Check_MK vorgaben entsprechen (Nur Buchstaben, Ziffern und Unterstriche sind erlaubt).',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_EXAMPLE_1'                         => '<strong>Beispiel</strong> Wenn Sie für den Objekt-Typ "Blade Server" definiert haben, das der Objekt-Typ als Merkmal zugewiesen werden soll, bekommen alle Blade Server ein "Blade_Server" Merkmal.',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_DESCRIPTION_2'                     => 'Zusätzlich zu den Attributen können Standort-Namen als Merkmal verwendet werden. Als generischer Standort kann ein Objekt-Typ definiert werden.',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_EXAMPLE_2'                         => '<strong>Beispiel</strong> Wenn wir einen Standort-Pfad wie z.B. "Deutschland (Land) > Berlin (Stadt) > Zentrale (Gebäude) > Serverraum (Raum) > Rack-02 (Schrank) > Server" haben, können wir definieren das der Name des obersten "Raum" Objekt-Typ als Merkmal exportiert wird, in diesem Fall "Serverraum".',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_EXPORT'                   => 'Generischen Standort-Merkmale exportieren',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_OBJ_TYPE'                 => 'Objekt-Typ als generischen Standort verwenden',
    'LC__MODULE__CHECK_MK__TAG_GUI__OVERWRITE_GLOBAL_DEFINITION'               => 'Globale Definition überschreiben',
    'LC__MODULE__CHECK_MK__TAG_GUI__GLOBAL_DEFINITION'                         => 'Globale Definition',
    'LC__MODULE__CHECK_MK__TAG_GUI__CATEGORY_DEFINITION'                       => 'Kategorie Definition',
    // Some dynamic GUI specific translations.
    'LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_DESCRIPTION'                       => 'In dieser Oberfläche lassen sich dynamische Merkmale definieren.<br />Ein dynamisches Merkmal wird automatisch den entsprechenden Objekten hinzugefügt, das der definierten Bedingung entspricht.',
    'LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_EXAMPLE_1'                         => '<strong>Beispiel 1</strong> Sie können definieren, das alle "Server" Objekte das Merkmal "server" erhalten sollen.',
    'LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_EXAMPLE_2'                         => '<strong>Beispiel 2</strong> Sie können definieren, das alle neu angelegten Objekte unterhalb des Standortes "Musterstadt" das Merkmal "standort-musterstadt" erhalten sollen.',
    'LC__MODULE__CHECK_MK__TAG_GUI__CONDITION'                                 => 'Bedingung',
    'LC__MODULE__CHECK_MK__TAG_GUI__PARAMETER'                                 => 'Parameter',
    'LC__MODULE__CHECK_MK__TAG_GUI__TAGS'                                      => 'Merkmal(e)',
    'LC__MODULE__CHECK_MK__TAG_GUI__ACTION'                                    => 'Aktion',
    // Exception translations.
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__NO_CONFIG'                    => 'Bitte definieren Sie mindestens einen Check_MK Host.',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__COULD_NOT_CREATE_SOCKET'      => 'Es konnte kein Socket zum Livestatus erstellt werden.',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__COULD_NOT_CONNECT_LIVESTATUS' => 'Es konnte keine Verbindung zum Livestatus hergestellt werden.',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET'   => 'Beim lesen der Daten ist ein Fehler aufgetreten: %s',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__INVALID_FORMAT'               => 'Das Abfrage-Ergebnis hat ein unbekanntes Format!',
    'LC__MODULE__CHECK_MK__EXPORT_EXCEPTION__NO_CONFIG'                        => 'Bitte definieren Sie mindestens einen Check_MK Host.',
    'LC__MODULE__CHECK_MK__EXPORT_EXCEPTION__NO_EXPORT_PATH_SET'               => 'Beim Host %s ist der  Export-Pfad nicht gesetzt - Der Standard-Pfad wird verwendet ("%s").',
    // Translations for the report view.
    'LC__MODULE__CHECK_MK__REPORT__TITLE'                                      => 'Check_MK Konsistenzprüfung',
    'LC__MODULE__CHECK_MK__REPORT__VIEW_TYPE'                                  => 'Check_MK',
    'LC__MODULE__CHECK_MK__REPORT__DESCRIPTION'                                => 'Auflistung aller Services so wie Konsistenzprüfung auf alle Beziehungen / Unterobjekte nach Check_MK Status',
    'LC__MODULE__CHECK_MK__REPORT__AFFECTED_OBJECTS'                           => 'Betroffene CMDB Objekte',
    'LC__MODULE__CHECK_MK__REPORT__INCONSISTENT_HOSTS'                         => 'Inkonsistente Hosts',
    'LC__MODULE__CHECK_MK__REPORT__DISPLAY_IN_CMDB_EXPLORER'                   => 'Service und hosts im CMDB-Explorer darstellen',
    'LC__MODULE__CHECK_MK__REPORT__RESPONSIBLE_SERVICES'                       => 'Verantwortliche Services',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_IT_SERVICES'                    => 'Laden der Services abgeschlossen!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_CHECK_MK_STATES'                => 'Check_MK Host Status werden geladen...',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_CHECK_MK_SERVICE_STATES'        => 'Check_MK Service Status werden geladen...',
    'LC__MODULE__CHECK_MK__REPORT__MQ__SUCCESS'                                => 'Erfolg!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FAIL'                                   => 'Fehlgeschlagen!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__NO_INCONSISTENCIES'                     => 'OK',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_INCONSISTENCIES'                  => 'Warnung: ',
    'LC__MODULE__CHECK_MK__REPORT__MQ__NO_INCONSISTENT_OBJECTS_BY_CMK'         => 'Es konnten keine inkonsistenten Objekte in Check_MK Status gefunden werden!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_INCONSISTENT_OBJECTS_BY_CMK'    => 'Lade inkonsistente Objekte nach Check_MK Status...',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_NO_INCONSISTENT_OBJECT'           => 'Es wurden <strong>keine inkonsistenten</strong> Objekte gefunden!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_ONE_INCONSISTENT_OBJECT'          => 'Es wurde <strong>ein inkonsistentes</strong> Objekt gefunden!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_X_INCONSISTENT_OBJECTS'           => 'Es wurden <strong>%d inkonsistente</strong> Objekte gefunden!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_NO_INCONSISTENT_SERVICES'         => 'Es wurden <strong>keine inkonsistenten</strong> Services gefunden!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_ONE_INCONSISTENT_SERVICE'         => 'Es wurde <strong>ein inkonsistenter</strong> Service gefunden!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_X_INCONSISTENT_SERVICES'          => 'Es wurden <strong>%d inkonsistente</strong> Services gefunden!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FINISHED_THE_REPORT_VIEW'               => 'Die Report View wurde fertig geladen!',

    'LC__MODULE__CHECK_MK__STATIC_TAGS__UNIQUE_NAME'  => 'Hostmerkmal (id)',
    'LC__MODULE__CHECK_MK__STATIC_TAGS__DISPLAY_NAME' => 'Anzeigename',
    'LC__MODULE__CHECK_MK__STATIC_TAGS__TAG_GROUP'    => 'Merkmal Gruppe',
    'LC__MODULE__CHECK_MK__STATIC_TAGS__EXPORTABLE'   => 'Wird exportiert',
    'LC__MODULE__CHECK_MK__NO_CONFIGURATION_SELECTED' => 'Es wurde keine Konfiguration ausgewählt!',
];