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
    'LC__MODULE__MAINTENANCE'                                             => 'Maintenance',
    'LC__LOGBOOK_SOURCE__MAINTENANCE'                                     => 'Maintenance',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT'                                => 'Maintenance annual report (Export)',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT_DESCRIPTION'                    => 'This report-view can be used to export a list of all maintenances in a given timeperiod(PDF)',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE'                         => 'Headline',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE'                          => 'Maintenance type',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO'                          => 'Logo for PDF header',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__FROM'                          => 'From',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__TO'                            => 'To',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__FILENAME'                      => 'annual-report',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__EXPORT'                        => 'PDF export & download',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__EXPORT_SUCCESS'                => 'Found <strong>:objects objects</strong> and <strong>:maintenances maintenances</strong> in the given timeperiod. They were exported to the PDF file.',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__EXPORT_SUCCESS_INFO'           => 'The PDF file "<strong>:filename</strong>" is located inside your i-doit cache directory and can be downloaded by clicking "Download".',
    'LC__REPORT__VIEW__MAINTENANCE_EXPORT__DOWNLOAD'                      => 'Download',
    'LC__CATG__VIRTUAL_MAINTENANCE'                                       => 'Maintenance overview',
    'LC__CATG__VIRTUAL_MAINTENANCE__MAINTENANCE'                          => 'Maintenance (link)',
    'LC__CATG__VIRTUAL_MAINTENANCE__MAINTENANCE_DATE'                     => 'Maintenance date',
    'LC__CATG__VIRTUAL_MAINTENANCE__FINISHED'                             => 'Completed?',
    'LC__CATG__VIRTUAL_MAINTENANCE__MAIL_SENT'                            => 'Email sent?',
    'LC__CATG__VIRTUAL_MAINTENANCE__LINK'                                 => 'Link to the maintenance',
    'LC__CATG__VIRTUAL_MAINTENANCE__NO_PLANNED_MAINTENANCES'              => 'There were no planned or finished maintenances found to this object.',
    'LC__CATG__VIRTUAL_MAINTENANCE__FILTER'                               => 'You can filter the maintenances by start date.',
    'LC__CATG__VIRTUAL_MAINTENANCE__FILTER_NEWEST_FIRST'                  => 'Newest maintenances first',
    'LC__CATG__VIRTUAL_MAINTENANCE__FILTER_OLDEST_FIRST'                  => 'Oldest maintenances first',
    'LC__MAINTENANCE__STATUS'                                             => 'Status',
    'LC__MAINTENANCE__OBJECT_IN_MAINTENANCE'                              => 'In maintenance',
    'LC__MAINTENANCE__PLANNING'                                           => 'Planning',
    'LC__MAINTENANCE__PLANNING__FINISH_MAINTENANCE'                       => 'Finish',
    'LC__MAINTENANCE__PLANNING__FINISHED'                                 => 'Finish maintenance',
    'LC__MAINTENANCE__PLANNING__MAIL_DISPATCHED'                          => 'Email dispatched',
    'LC__MAINTENANCE__PLANNING__OBJECT_SELECTION'                         => 'Objects',
    'LC__MAINTENANCE__PLANNING__TYPE'                                     => 'Maintenance type',
    'LC__MAINTENANCE__PLANNING__DATE_FROM'                                => 'Maintenance date',
    'LC__MAINTENANCE__PLANNING__DATE_TO'                                  => 'Maintenance date',
    'LC__MAINTENANCE__PLANNING__COMMENT'                                  => 'Commenct',
    'LC__MAINTENANCE__PLANNING__CONTACTS'                                 => 'Recipient',
    'LC__MAINTENANCE__PLANNING__CONTACT_ROLES'                            => 'Assigned roles',
    'LC__MAINTENANCE__PLANNING__CONTACT_ROLES_INFO'                       => 'If there is a role assigned to one of the objects every contact behind this role will be added to the list of recipients.',
    'LC__MAINTENANCE__PLANNING__MAILTEMPLATE'                             => 'Email template',
    'LC__MAINTENANCE__PLANNING_ARCHIVE'                                   => 'Planning (archive)',
    'LC__MAINTENANCE__TYPE__CLIENT_MAINTENANCE'                           => 'Client maintenance',
    'LC__MAINTENANCE__OVERVIEW'                                           => 'Overview',
    'LC__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM'                        => 'From',
    'LC__MAINTENANCE__OVERVIEW__FILTER__DATE_TO'                          => 'To',
    'LC__MAINTENANCE__OVERVIEW__FILTER_BUTTON'                            => 'Filter',
    'LC__MAINTENANCE__OVERVIEW__FILTER_RESET_BUTTON'                      => 'Reset',
    'LC__MAINTENANCE__OVERVIEW__LIST_PAST'                                => 'In the past',
    'LC__MAINTENANCE__OVERVIEW__LIST_THIS_WEEK'                           => 'This week',
    'LC__MAINTENANCE__OVERVIEW__LIST_THIS_MONTH'                          => 'This month',
    'LC__MAINTENANCE__OVERVIEW__LIST_NEXT_WEEK'                           => 'Next week',
    'LC__MAINTENANCE__OVERVIEW__LIST_NEXT_MONTH'                          => 'Next month',
    'LC__MAINTENANCE__OVERVIEW__LIST_FUTURE'                              => 'In the future',
    'LC__MAINTENANCE__OVERVIEW__FINISHED'                                 => 'Completed!',
    'LC__MAINTENANCE__MAILTEMPLATE'                                       => 'Email template',
    'LC__MAINTENANCE__MAILTEMPLATE__TITLE'                                => 'Title',
    'LC__MAINTENANCE__MAILTEMPLATE__TEXT'                                 => 'Text',
    'LC__MAINTENANCE__MAILTEMPLATE__TEXT_VARIABLES'                       => 'Available placeholders',
    'LC__MAINTENANCE__MAILTEMPLATES'                                      => 'Email templates',
    'LC__MAINTENANCE__POPUP__NO_MAINTENANCES_SELECTED'                    => 'Attention! You have selected no maintenances.',
    'LC__MAINTENANCE__POPUP__FINISH_COMMENT'                              => 'Logbook comment',
    'LC__MAINTENANCE__POPUP__MAINTENANCES_TO_FINISH'                      => 'Finishing maintenances',
    'LC__MAINTENANCE__POPUP__FINISH_MAINTENANCE'                          => 'Finish maintenance',
    'LC__MAINTENANCE__SEND_MAIL'                                          => 'Send email',
    'LC__MAINTENANCE__SEND_MAIL_NO_PLANNING_SELECTED'                     => 'Please select at least one maintenance plan.',
    'LC__MAINTENANCE__SEND_MAIL_CONFIRM'                                  => 'Do you want to send the maintenance mail(s) to all involved contacts and person groups?',
    'LC__MAINTENANCE__SEND_MAIL_AGAIN_CONFIRM'                            => 'Attention! You have selected maintenance plans, whose emails have already been sent (%s). Do you still want to send the maintenance mail(s) to all involved contacts and person groups?',
    'LC__MAINTENANCE__SEND_MAIL_SUCCESS'                                  => 'The emails have been sent successfully!',
    'LC__MAINTENANCE__SEND_MAIL_FAILURE'                                  => 'While sending the emails, errors occured: ',
    'LC__MAINTENANCE__AUTH__PLANNING'                                     => 'Planning',
    'LC__MAINTENANCE__AUTH__PLANNING_ARCHIVE'                             => 'Planning (archive)',
    'LC__MAINTENANCE__AUTH__MAILTEMPLATE'                                 => 'Email template',
    'LC__MAINTENANCE__AUTH__OVERVIEW'                                     => 'Overview',
    'LC__MAINTENANCE__AUTH__SEND_MAILS'                                   => 'Send emails',
    'LC__MAINTENANCC__EXCEPTION__NO_MAILTEMPLATE_SELECTED'                => 'You have selected no email template',
    'LC__MAINTENANCC__EXCEPTION__NO_OBJECTS_SELECTED'                     => 'You have selected no objects for this maintenance',
    'LC__MAINTENANCC__EXCEPTION__NO_RECIPIENTS'                           => 'Couldn\'t find any recipients',
    'LC__MAINTENANCE__NOTIFY__SAVE_SUCCESS'                               => 'Saved successfully!',
    'LC__MAINTENANCE__NOTIFY__SAVE_FAILURE'                               => 'Error while saving: ',
    'LC__MAINTENANCE__NOTIFY__COMPLETED_SUCCESSFULLY'                     => 'The maintenance(s) have been finished!',
    'LC__MAINTENANCE__NOTIFY__NO_MAINTENANCES_FOUND_IN_THIS_TIMEPERIOD'   => 'There wo no maintenances found in the given timeperiod',
    'LC__MAINTENANCE__PDF__TITLE'                                         => 'List of IT-Systems',
    'LC__MAINTENANCE__PDF__PAGE'                                          => 'Page',
    'LC__MAINTENANCE__PDF__FINISHED'                                      => 'Completed',
    'LC__MAINTENANCE__PDF__PERSON'                                        => 'Person',
    'LC__MAINTENANCE__PDF__PERSON_ROLE'                                   => 'Role',
    'LC__MAINTENANCE__PDF__EXPORTED'                                      => 'The PDF file was created successfully!',
    'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS'                         => 'Get email addresses from',
    'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS__RESOLVE_CONTACT_GROUPS' => 'Resolve contact groups and notify each person individually',
    'LC__MAINTENANCE__SETTINGS__EMAIL_RECIPIENTS__ONLY_SELECTED_CONTACTS' => 'Simply use the selected contacts without resolving contact groups',
];