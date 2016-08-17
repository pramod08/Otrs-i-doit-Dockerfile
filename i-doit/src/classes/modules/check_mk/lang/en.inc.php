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
    'LC__MODULE__CHECK_MK__CONFIGURATION'                                      => 'Configuration',
    'LC__MODULE__CHECK_MK__EXPORT'                                             => 'Check_MK Export',
    'LC__MODULE__CHECK_MK__EXPORT__PATH'                                       => 'Export path',
    'LC__MODULE__CHECK_MK__START_EXPORT'                                       => 'Start the export',
    'LC__MODULE__CHECK_MK__START_SHELLSCRIPT'                                  => 'Start the export and trigger transfer shellscript',
    'LC__MODULE__CHECK_MK__START_SHELLSCRIPT_DESCRIPTION'                      => 'The "transfer shellscript" is located in the i-doit directory and can be customized. The script will try to transfer all the exported Check_MK configuration files to the actual Check_MK host.',
    'LC__MODULE__CHECK_MK__WAITING'                                            => 'Ready to export!',
    'LC__MODULE__CHECK_MK__EXPORTED_FILES'                                     => 'Exported configuration:',
    'LC__MODULE__CHECK_MK__CMK_INSTANCE'                                       => 'Check_MK instance',
    'LC__MODULE__CHECK_MK__HOST'                                               => 'Host',
    'LC__MODULE__CHECK_MK__HOSTS'                                              => 'Hosts',
    'LC__MODULE__CHECK_MK__HOSTS__NEW_HOST'                                    => 'Create a new host',
    'LC__MODULE__CHECK_MK__LIVESTATUS'                                         => 'Livestatus',
    'LC__MODULE__CHECK_MK__LIVESTATUS__ACTIVE'                                 => 'Active',
    'LC__MODULE__CHECK_MK__LIVESTATUS__TYPE'                                   => 'Type',
    'LC__MODULE__CHECK_MK__LIVESTATUS__PATH'                                   => 'Path',
    'LC__MODULE__CHECK_MK__LIVESTATUS__ADDRESS'                                => 'Address',
    'LC__MODULE__CHECK_MK__LIVESTATUS__PORT'                                   => 'Port',
    'LC__MODULE__CHECK_MK__TAGS__NO_TAGS'                                      => 'There are no configured tags yet',
    'LC__MODULE__CHECK_MK__EXPORT_STRUCTURE'                                   => 'Export structure',
    'LC__MODULE__CHECK_MK__EXPORT_LANGUAGE'                                    => 'Export language',
    'LC__MODULE__CHECK_MK__EXPORT_LANGUAGE_ALL_AVAILABLE'                      => 'All available',
    'LC__MODULE__CHECK_MK__EXPORT_WITHOUT_STRUCTURE'                           => 'None (all files in one directory)',
    'LC__MODULE__CHECK_MK__EXPORT_IN_LOCATION_PATH'                            => 'Directories by physical location',
    'LC__MODULE__CHECK_MK__EXPORT_IN_LOG_LOCATION_PATH'                        => 'Directories by logical location',
    'LC__MODULE__CHECK_MK__EXPORT_OBJECT_TYPES'                                => 'Directories by object types',
    'LC__MODULE__CHECK_MK__EXPORT_PATH_WARNING'                                => 'Attention: The defined export-directories %s and all of their content will be deleted during the export!',
    // Translations for the Auth GUI.
    'LC__AUTH_GUI__CONFIGURATION'                                              => 'Check_MK configuration',
    'LC__AUTH_GUI__EXPORT'                                                     => 'Configuration export',
    'LC__AUTH_GUI__TAG_CONFIG'                                                 => 'Tag configuration',
    'LC__AUTH__CHECK_MK_EXCEPTION__MISSING_RIGHT_FOR_CONFIGURATION'            => 'You are not allowed to open the Check_MK configuration.',
    'LC__AUTH__CHECK_MK_EXCEPTION__MISSING_RIGHT_FOR_EXPORT'                   => 'You are not allowed to export the Check_MK configuration.',
    'LC__AUTH__CHECK_MK_EXCEPTION__MISSING_RIGHT_FOR_TAGS'                     => 'You are not allowed to open the Check_MK Tag configuration',
    // Adding some translations for the category.
    'LC__CATG__CMK'                                                            => 'Check_MK',
    'LC__CATG__CMK_DEF'                                                        => 'Export parameter',
    'LC__CATG__CMK_FOLDER'                                                     => 'Check_MK (Host)',
    'LC__CATG__CMK_TAG'                                                        => 'Host tags',
    'LC__CATG__CMK_TAG_DYNAMIC'                                                => 'Host tags (dynamic)',
    'LC__CATG__CMK__ACTIVE'                                                    => 'Active',
    'LC__CATG__CMK__ALIAS'                                                     => 'Alias',
    'LC__CATG__CMK__HOSTNAME'                                                  => 'Hostname',
    'LC__CATG__CMK__EXPORT_IP'                                                 => 'Export IP address',
    'LC__CATG__CMK_TAG__ADD_TAG'                                               => 'Add tag',
    'LC__CATG__CMK_TAG__TAGS'                                                  => 'Host tags',
    'LC__CATG__CMK_TAG__CMDB_TAGS'                                             => 'CMDB tags',
    'LC__CATG__CMK_TAG__NO_CMDB_TAGS'                                          => 'There are no CMDB tags',
    'LC__CATG__CMK_TAG__DYNAMIC_TAGS'                                          => 'Dynamic tags',
    'LC__CATG__CMK_TAG__NO_DYNAMIC_TAGS'                                       => 'No dynamic tags',
    'LC__CATG__CMK_HOST_SERVICE'                                               => 'Service assignment',
    'LC__CATG__CMK_SERVICE'                                                    => 'Service assignment',
    'LC__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT'                               => 'Software assignment',
    'LC__CATG__CMK_SERVICE__INHERITED_SERVICES'                                => 'Inherited services via Software assignment',
    'LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES'                                 => 'Depends on',
    'LC__CATG__CMK_SERVICE__NO_SERVICES'                                       => 'No services were found!',
    'LC__CATG__CMK_SERVICE_FOLDER'                                             => 'Check_MK (Service)',
    'LC__CATG__CHECK_MK_FOLDER'                                                => 'Check_MK',
    // Translations for a few dynamic conditions.
    'LC__MODULE__CHECK_MK__TAG_GROUP__AGENT_TYPE'                              => 'Agent type',
    'LC__MODULE__CHECK_MK__TAGS__CONDITION__NEW_OBJECTS_OF_TYPE'               => 'Objects of type',
    'LC__MODULE__CHECK_MK__TAGS__CONDITION__LOCATION'                          => 'Objects inside location',
    'LC__MODULE__CHECK_MK__TAGS__CONDITION__PURPOSE'                           => 'Objects with purpose',
    'LC__MODULE__CHECK_MK__TAGS__ADD_DYNAMIC_TAG'                              => 'Add dynamic tag',
    'LC__MODULE__CHECK_MK__TAGS__DYNAMIC_TAG'                                  => 'Dynamic tag',
    'LC__MODULE__CHECK_MK__TAGS__DYNAMIC_TAG_ASSIGNMENT'                       => 'Assign dynamic tags',
    'LC__MODULE__CHECK_MK__TAGS__CMDB_TAG_CONFIG'                              => 'Configure CMDB tags',
    'LC__MODULE__CHECK_MK__TAGS__STATIC_TAG_CONFIG'                            => 'Host tags (static)',
    // Some CMDB GUI specific translations.
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_DESCRIPTION'                       => 'CMDB tags are configured through simple rules and will be displayed inside the "Check_MK tags" category and the configuration-export. CMDB tags can not be created or changed by hand, the i-doit core will handle everything.<br />The CMDB tags are based on already configured category data (for example the object-type title, the objects "purpose", "category" etc.) - the properties will be formatted by i-doit, so they fit the Check_MK definition (only letters, digits and underscores).',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_EXAMPLE_1'                         => '<strong>Example</strong> If you configure that the object-type shall be exported as tag for all "Blade Server", then all of your Blade Servers will automatically receive a "Blade_Server" tag.',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_DESCRIPTION_2'                     => 'Additionally to the properties locations can defined as generic tag source. As a generic location you can select an object-type.',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_EXAMPLE_2'                         => '<strong>Example</strong> If we have a location path like for example "Germany (Country) > Berlin (City) > Headquarters (Building) > Server room (Room) > Rack-02 (Rack) > Server", we could define that the name of the topmost "Room" shall serve as tag, in this example "Server_room".',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_EXPORT'                   => 'Export generic location-tag',
    'LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_OBJ_TYPE'                 => 'Object-type to use as generic location',
    'LC__MODULE__CHECK_MK__TAG_GUI__OVERWRITE_GLOBAL_DEFINITION'               => 'Overwrite global definition',
    'LC__MODULE__CHECK_MK__TAG_GUI__GLOBAL_DEFINITION'                         => 'Global definition',
    'LC__MODULE__CHECK_MK__TAG_GUI__CATEGORY_DEFINITION'                       => 'Category definition',
    // Some dynamic GUI specific translations.
    'LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_DESCRIPTION'                       => 'Here you can define dynamic tags.<br />A dynamic tag will be assigned automatically, whenever a object fits the given conditions.',
    'LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_EXAMPLE_1'                         => '<strong>Example 1</strong> You can define that all objects of type "Server" shall receive the "server" tag.',
    'LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_EXAMPLE_2'                         => '<strong>Example 2</strong> You can configure, that all objects underneath the "Musterstadt" location shall receive the "standort-musterstadt" tag.',
    'LC__MODULE__CHECK_MK__TAG_GUI__CONDITION'                                 => 'Condition',
    'LC__MODULE__CHECK_MK__TAG_GUI__PARAMETER'                                 => 'Parameter',
    'LC__MODULE__CHECK_MK__TAG_GUI__TAGS'                                      => 'Tag(s)',
    'LC__MODULE__CHECK_MK__TAG_GUI__ACTION'                                    => 'Action',
    // Exception translations.
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__NO_CONFIG'                    => 'Please configure at least one Check_MK host.',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__COULD_NOT_CREATE_SOCKET'      => 'Could not create socket to livestatus.',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__COULD_NOT_CONNECT_LIVESTATUS' => 'Unable to connect to livestatus',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET'   => 'Problem while reading from socket: %s',
    'LC__MODULE__CHECK_MK__LIVESTATUS_EXCEPTION__INVALID_FORMAT'               => 'The response has an invalid format!',
    'LC__MODULE__CHECK_MK__EXPORT_EXCEPTION__NO_CONFIG'                        => 'Please configure at least one Check_MK host.',
    'LC__MODULE__CHECK_MK__EXPORT_EXCEPTION__NO_EXPORT_PATH_SET'               => 'The export dir for host %s is empty - The default will be used ("%s").',
    // Translations for the report view.
    'LC__MODULE__CHECK_MK__REPORT__TITLE'                                      => 'Check_MK consistency check',
    'LC__MODULE__CHECK_MK__REPORT__VIEW_TYPE'                                  => 'Check_MK',
    'LC__MODULE__CHECK_MK__REPORT__DESCRIPTION'                                => 'Lists all IT services aswell as consistency checks on all relations / child objects for the Check_MK status',
    'LC__MODULE__CHECK_MK__REPORT__AFFECTED_OBJECTS'                           => 'Affected CMDB objects',
    'LC__MODULE__CHECK_MK__REPORT__INCONSISTENT_HOSTS'                         => 'Inconsisent hosts',
    'LC__MODULE__CHECK_MK__REPORT__DISPLAY_IN_CMDB_EXPLORER'                   => 'Display services and hosts in the CMDB-explorer',
    'LC__MODULE__CHECK_MK__REPORT__RESPONSIBLE_SERVICES'                       => 'Responsible services',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_IT_SERVICES'                    => 'Loaded all services!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_CHECK_MK_STATES'                => 'Loading Check_MK host states...',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_CHECK_MK_SERVICE_STATES'        => 'Loading Check_MK service states...',
    'LC__MODULE__CHECK_MK__REPORT__MQ__SUCCESS'                                => 'Success!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FAIL'                                   => 'Failed!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__NO_INCONSISTENCIES'                     => 'OK',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_INCONSISTENCIES'                  => 'Warning: ',
    'LC__MODULE__CHECK_MK__REPORT__MQ__NO_INCONSISTENT_OBJECTS_BY_CMK'         => 'No inconsistent objects found via Check_MK statue!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__LOADING_INCONSISTENT_OBJECTS_BY_CMK'    => 'Loading inconsistent objects by check_mk status...',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_NO_INCONSISTENT_OBJECT'           => 'Found <strong>no inconsistent</strong> hosts!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_ONE_INCONSISTENT_OBJECT'          => 'Found <strong>one inconsistent</strong> host!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_X_INCONSISTENT_OBJECTS'           => 'Found <strong>%d inconsistent</strong> hosts!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_NO_INCONSISTENT_SERVICES'         => 'Found <strong>no inconsistent</strong> services!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_ONE_INCONSISTENT_SERVICE'         => 'Found <strong>one inconsistent</strong> service!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FOUND_X_INCONSISTENT_SERVICES'          => 'Found <strong>%d inconsistent</strong> services!',
    'LC__MODULE__CHECK_MK__REPORT__MQ__FINISHED_THE_REPORT_VIEW'               => 'Finished loading the report view!',

    'LC__MODULE__CHECK_MK__STATIC_TAGS__UNIQUE_NAME'  => 'Host tag (id)',
    'LC__MODULE__CHECK_MK__STATIC_TAGS__DISPLAY_NAME' => 'Display name',
    'LC__MODULE__CHECK_MK__STATIC_TAGS__TAG_GROUP'    => 'Host group',
    'LC__MODULE__CHECK_MK__STATIC_TAGS__EXPORTABLE'   => 'To be exported',
    'LC__MODULE__CHECK_MK__NO_CONFIGURATION_SELECTED' => 'You need to select a configuration!',
];