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
 * "Maintenance" Module language file
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.1
 */

return [
    'LC__MODULE__MAINTENANCE'                                             => 'Wartung',
    'LC__LOGBOOK_SOURCE__MAINTENANCE'                                     => 'Wartung',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT'                                => 'Wartung Jahresbericht (Export)',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT_DESCRIPTION'                    => 'Diese Report-View kann benutzt werden um eine Liste aller Wartungen zwischen zwei Zeitpunkten zu exportieren (PDF)',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE'                         => 'Überschrift',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE'                          => 'Wartungstyp',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO'                          => 'Logo für PDF',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__FROM'                          => 'Von',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__TO'                            => 'Bis',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__FILENAME'                      => 'jahresbericht',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__EXPORT'                        => 'PDF Export & Download',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__EXPORT_SUCCESS'                => 'Es wurden <strong>:objects Objekte</strong> und <strong>:maintenances Wartungen</strong> im angegebenen Zeitraum gefunden und in der PDF Datei exportiert.',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__EXPORT_SUCCESS_INFO'           => 'Die PDF Datei "<strong>:filename</strong>" befindet sich im Cache Verzeichnis und kann per Klick auf "Download" heruntergeladen werden.',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__DOWNLOAD'                      => 'Download',
    'LC__CATG__VIRTUAL_MAINTENANCE'                                       => 'Wartungsübersicht',
    'LC__CATG__VIRTUAL_MAINTENANCE__MAINTENANCE'                          => 'Wartung (Link)',
    'LC__CATG__VIRTUAL_MAINTENANCE__MAINTENANCE_DATE'                     => 'Wartungstermin',
    'LC__CATG__VIRTUAL_MAINTENANCE__FINISHED'                             => 'Abgeschlossen?',
    'LC__CATG__VIRTUAL_MAINTENANCE__MAIL_SENT'                            => 'E-Mail abgeschickt?',
    'LC__CATG__VIRTUAL_MAINTENANCE__LINK'                                 => 'Link zur Wartung',
    'LC__CATG__VIRTUAL_MAINTENANCE__NO_PLANNED_MAINTENANCES'              => 'Zu diesem Objekt konnten keine geplanten oder ausgeführten Wartungen gefunden werden.',
    'LC__CATG__VIRTUAL_MAINTENANCE__FILTER'                               => 'Sie können die Wartungen nach Startdatum filtern.',
    'LC__CATG__VIRTUAL_MAINTENANCE__FILTER_NEWEST_FIRST'                  => 'Neuste Wartungstermine zuerst',
    'LC__CATG__VIRTUAL_MAINTENANCE__FILTER_OLDEST_FIRST'                  => 'Älteste Wartungstermine zuerst',
    'LC__MAINTENANCE__STATUS'                                             => 'Status',
    'LC__MAINTENANCE__OBJECT_IN_MAINTENANCE'                              => 'In Wartung',
    // "Wird gewartet" ?
    'LC__MAINTENANCE__PLANNING'                                           => 'Planung',
    'LC__MAINTENANCE__PLANNING__FINISH_MAINTENANCE'                       => 'Abschließen',
    'LC__MAINTENANCE__PLANNING__FINISHED'                                 => 'Wartung abgeschlossen',
    'LC__MAINTENANCE__PLANNING__MAIL_DISPATCHED'                          => 'E-Mail abgeschickt',
    'LC__MAINTENANCE__PLANNING__OBJECT_SELECTION'                         => 'Objekte',
    'LC__MAINTENANCE__PLANNING__TYPE'                                     => 'Wartungstyp',
    'LC__MAINTENANCE__PLANNING__DATE_FROM'                                => 'Wartungstermin',
    'LC__MAINTENANCE__PLANNING__DATE_TO'                                  => 'Wartungstermin',
    'LC__MAINTENANCE__PLANNING__COMMENT'                                  => 'Kommentar',
    'LC__MAINTENANCE__PLANNING__CONTACTS'                                 => 'Empfänger',
    'LC__MAINTENANCE__PLANNING__CONTACT_ROLES'                            => 'Zugewiesene Rollen',
    'LC__MAINTENANCE__PLANNING__CONTACT_ROLES_INFO'                       => 'Es werden diejenigen Rollen berücksichtigt, die dem jeweiligen Objekt als Kontakt zugewiesen sind.',
    'LC__MAINTENANCE__PLANNING__MAILTEMPLATE'                             => 'E-Mail Vorlage',
    'LC__MAINTENANCE__PLANNING_ARCHIVE'                                   => 'Planung (Archiv)',
    'LC__MAINTENANCE__TYPE__CLIENT_MAINTENANCE'                           => 'Clientwartung',
    'LC__MAINTENANCE__OVERVIEW'                                           => 'Übersicht',
    'LC__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM'                        => 'Von',
    'LC__MAINTENANCE__OVERVIEW__FILTER__DATE_TO'                          => 'Bis',
    'LC__MAINTENANCE__OVERVIEW__FILTER_BUTTON'                            => 'Filtern',
    'LC__MAINTENANCE__OVERVIEW__FILTER_RESET_BUTTON'                      => 'Zurücksetzen',
    'LC__MAINTENANCE__OVERVIEW__LIST_PAST'                                => 'In der Vergangenheit',
    'LC__MAINTENANCE__OVERVIEW__LIST_THIS_WEEK'                           => 'Diese Woche',
    'LC__MAINTENANCE__OVERVIEW__LIST_THIS_MONTH'                          => 'Diesen Monat',
    'LC__MAINTENANCE__OVERVIEW__LIST_NEXT_WEEK'                           => 'Nächste Woche',
    'LC__MAINTENANCE__OVERVIEW__LIST_NEXT_MONTH'                          => 'Nächsten Monat',
    'LC__MAINTENANCE__OVERVIEW__LIST_FUTURE'                              => 'In der Zukunft',
    'LC__MAINTENANCE__OVERVIEW__FINISHED'                                 => 'Abgeschlossen!',
    'LC__MAINTENANCE__MAILTEMPLATE'                                       => 'E-Mail Vorlage',
    'LC__MAINTENANCE__MAILTEMPLATE__TITLE'                                => 'Titel',
    'LC__MAINTENANCE__MAILTEMPLATE__TEXT'                                 => 'Inhalt',
    'LC__MAINTENANCE__MAILTEMPLATE__TEXT_VARIABLES'                       => 'Mögliche Platzhalter',
    'LC__MAINTENANCE__MAILTEMPLATES'                                      => 'E-Mail Vorlagen',
    'LC__MAINTENANCE__POPUP__NO_MAINTENANCES_SELECTED'                    => 'Achtung! Sie haben keine Wartung ausgewählt.',
    'LC__MAINTENANCE__POPUP__FINISH_COMMENT'                              => 'Logbuch Kommentar',
    'LC__MAINTENANCE__POPUP__MAINTENANCES_TO_FINISH'                      => 'Abzuschließende Wartungen',
    'LC__MAINTENANCE__POPUP__FINISH_MAINTENANCE'                          => 'Wartung abschließen',
    'LC__MAINTENANCE__SEND_MAIL'                                          => 'E-Mails abschicken',
    'LC__MAINTENANCE__SEND_MAIL_NO_PLANNING_SELECTED'                     => 'Bitte wählen Sie zunächst mindestens eine Wartung aus.',
    'LC__MAINTENANCE__SEND_MAIL_CONFIRM'                                  => 'Möchten Sie die Wartungs E-Mails an alle beteiligten Personen und Personengruppen schicken?',
    'LC__MAINTENANCE__SEND_MAIL_AGAIN_CONFIRM'                            => 'Achtung! Sie haben Wartungen ausgewählt, deren E-Mail bereits verschickt wurde (%s). Möchten Sie die Wartungs E-Mails trotzdem an alle beteiligten Personen und Personengruppen schicken?',
    'LC__MAINTENANCE__SEND_MAIL_SUCCESS'                                  => 'Die E-Mails wurden erfolgreich verschickt!',
    'LC__MAINTENANCE__SEND_MAIL_FAILURE'                                  => 'Beim senden der E-Mails sind Fehler aufgetreten: ',
    'LC__MAINTENANCE__AUTH__PLANNING'                                     => 'Planung',
    'LC__MAINTENANCE__AUTH__PLANNING_ARCHIVE'                             => 'Planung (Archiv)',
    'LC__MAINTENANCE__AUTH__MAILTEMPLATE'                                 => 'E-Mail Vorlagen',
    'LC__MAINTENANCE__AUTH__OVERVIEW'                                     => 'Übersicht',
    'LC__MAINTENANCE__AUTH__SEND_MAILS'                                   => 'E-Mails senden',
    'LC__MAINTENANCC__EXCEPTION__NO_MAILTEMPLATE_SELECTED'                => 'Sie haben kein E-Mail Vorlage ausgewählt',
    'LC__MAINTENANCC__EXCEPTION__NO_OBJECTS_SELECTED'                     => 'Sie haben kein Objekte für diese Wartung ausgewählt',
    'LC__MAINTENANCC__EXCEPTION__NO_RECIPIENTS'                           => 'Es konnten keine Empfänger gefunden werden',
    'LC__MAINTENANCE__NOTIFY__SAVE_SUCCESS'                               => 'Erfolgreich gespeichert!',
    'LC__MAINTENANCE__NOTIFY__SAVE_FAILURE'                               => 'Fehler beim speichern: ',
    'LC__MAINTENANCE__NOTIFY__COMPLETED_SUCCESSFULLY'                     => 'Die Wartung(en) wurden erfolgreich abgeschlossen!',
    'LC__MAINTENANCE__NOTIFY__NO_MAINTENANCES_FOUND_IN_THIS_TIMEPERIOD'   => 'Im gewählten Zeitraum wurden keine Wartungen gefunden',
    'LC__MAINTENANCE__PDF__TITLE'                                         => 'Liste der IT-Systeme',
    'LC__MAINTENANCE__PDF__PAGE'                                          => 'Seite',
    'LC__MAINTENANCE__PDF__FINISHED'                                      => 'Abgeschlossen',
    'LC__MAINTENANCE__PDF__PERSON'                                        => 'Person',
    'LC__MAINTENANCE__PDF__PERSON_ROLE'                                   => 'Rolle',
    'LC__MAINTENANCE__PDF__EXPORTED'                                      => 'Die PDF Datei wurde erfolgreich erstellt!',
    'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS'                         => 'E-Mail Adressen entnehmen aus',
    'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS__RESOLVE_CONTACT_GROUPS' => 'Kontaktgruppen auflösen und Mitglieder einzeln Benachrichtigen',
    'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS__ONLY_SELECTED_CONTACTS' => 'E-Mail-Adressen der ausgewählten Kontakte ohne auflösung verwenden',
];