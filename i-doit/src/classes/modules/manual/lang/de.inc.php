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

return [
    /* Tree */
    'LC__MANUAL'                                                => 'Dokumente',
    'LC__MANUAL__DOCUMENTS'                                     => 'Dokumente',
    'LC__MANUAL__CHAPTERS_OF_DOCUMENT'                          => 'Kapitel',
    'LC__MANUAL__DOCUMENT_TEMPLATES'                            => 'Dokument-Vorlagen',

    /* Document-List */
    'LC__MANUAL__DOCUMENT_LIST__ID'                             => 'ID',
    'LC__MANUAL__DOCUMENT_LIST__TITLE'                          => 'Bezeichnung',
    'LC__MANUAL__DOCUMENT_LIST__TYPE'                           => 'Kategorie',

    /* Chapters-List */
    'LC__MANUAL__CHAPTER_LIST__ID'                              => 'ID',
    'LC__MANUAL__CHAPTER_LIST__TITLE'                           => 'Bezeichnung',
    'LC__MANUAL__CHAPTER_LIST__PATTERN'                         => 'Baustein',

    /* Document-Template FORM */
    'LC__MANUAL__DOCUMENT_TEMPLATE__CREATION_EDIT'              => 'Dokument erstellen/bearbeiten',
    'LC__MANUAL__DOCUMENT_TEMPLATE__TYPE'                       => 'Dokument-Art',
    'LC__MANUAL__DOCUMENT_TEMPLATE__CHAPTER__OVERVIEW'          => 'Kapitelübersicht',
    'LC__MANUAL__DOCUMENT_TEMPLATE__ADD_CHAPTER'                => 'Kapitel hinzufügen',
    'LC__MANUAL__DOCUMENT_TEMPLATE__EDIT_CHAPTER'               => 'Kapitel bearbeiten',
    'LC__MANUAL__DOCUMENT_TEMPLATE__INHERITS'                   => 'erbt von',
    'LC__MANUAL__DOCUMENT_TEMPLATE__HEADER'                     => 'Kopfzeile',
    'LC__MANUAL__DOCUMENT_TEMPLATE__FOOTER'                     => 'Fußzeile',

    /* Chapter FORM */
    'LC__MANUAL__CHAPTER__CREATION_EDIT'                        => 'Kapitel erstellen/bearbeiten',
    'LC__MANUAL__CHAPTER__PARENT_CHAPTER'                       => 'übergeordnetes Kapitel',
    'LC__MANUAL__CHAPTER__TEMPLATE_CHAPTER'                     => 'Kapitelvorlage',
    'LC__MANUAL__CHAPTER__COPY'                                 => 'Kopieren',

    /* Popup: Add Chapter */
    'LC__MANUAL__POPUP_CHAPTER__CREATE__CHAPTER'                => 'Neues Kapitel erstellen',
    'LC__MANUAL__POPUP_CHAPTER__CREATE__SAVE_COMPONENT_CONTENT' => 'Kapitelinhalt kopieren',

    /* Popup: Delete Chapter */
    'LC__MANUAL__POPUP__REMOVE'                                 => 'Wollen Sie wirklich fortfahren?',
    'LC__MANUAL__POPUP__REMOVE__TEXT'                           => 'Bitte vergewissern Sie sich, dass das zu löschende Kapitel keine Unterkapitel beinhaltet. Ansonsten werden diese ebenfalls entfernt.',
    'LC__MANUAL__DOCUMENT_TEMPLATE__DELETE_CHAPTER'             => 'Kapitel löschen',

    /* Popup: Placeholder ExternalObject */
    'LC__MANUAL__POPUP_P__MAINOBJECT__HEADER'                   => 'Hauptobjekt',

    /* Popup: Placeholder ExternalObject */
    'LC__MANUAL__POPUP_P__EXTERNALOBJECT__HEADER'               => 'Externes Objekt',
    'LC__MANUAL__POPUP_P__EXTERNALOBJECT__CATG_PROPERTY'        => 'Kategorie-Eigenschaft',
    'LC__MANUAL__POPUP_P__EXTERNALOBJECT__CATG_ENTRY'           => 'Kategorie-Eintrag',

    /* Popup: Placeholder Report */
    'LC__MANUAL__POPUP_P__REPORT__HEADER'                       => 'Report',

    /* Popup: Placeholder Template Var */
    'LC__MANUAL__POPUP_P__TEMPLATE_VAR'                         => 'Template-Variablen',
    'LC__MANUAL__POPUP_P__TEMPLATE_VAR__CREATION_DATE'          => 'Erstellungsdatum',
    'LC__MANUAL__POPUP_P__TEMPLATE_VAR__CHAPTER_TITLE'          => 'Kapitel-Titel',
    'LC__MANUAL__POPUP_P__TEMPLATE_VAR__DOCUMENT_TITLE'         => 'Dokument-Titel',
    'LC__MANUAL__POPUP_P__TEMPLATE_VAR__DOCUMENT_TYPE'          => 'Dokument-Typ',
    'LC__MANUAL__POPUP_P__TEMPLATE_VAR__AUTHOR'                 => 'Autor',

    /* Document-Type LCs */
    'LC__MANUAL__DOCUMENT_TYPE__BHB'                            => 'Betriebshandbuch',
    'LC__MANUAL__DOCUMENT_TYPE__TECHNICAL'                      => 'Technische Dokumentation',
    'LC__MANUAL__DOCUMENT_TYPE__USER'                           => 'Benutzerdokumentation',
    'LC__MANUAL__DOCUMENT_TYPE__EMERGENCY'                      => 'Notfallhandbuch',
    'LC__MANUAL__DOCUMENT_TYPE__CONCEPT'                        => 'Konzept',

    /* Chapter FORM */
    'LC__CHAPTER__CREATION_EDIT'                                => 'Kapitel bearbeiten/anlegen',
    'LC__MANUAL__CHAPTER__PATTERN'                              => 'Kapitel-Baustein',
    'LC__MANUAL__CHAPTER__PARENT_CHAPTER'                       => 'übergeordnetes Kapitel',

    'LC__MANUAL__CHAPTER__COMPONENT'                          => 'Vorlage',

    /* Popup: Document-Creation */
    'LC__MANUAL__DOCUMENT__DOCUMENT_TEMPLATE'                 => 'Dokument-Template',
    'LC__MANUAL__DOCUMENT__DOCUMENT_TYPE'                     => 'Dokument-Art',
    'LC__MANUAL__DOCUMENT__CREATE_DOCUMENT'                   => 'Neues Dokument erstellen',
    'LC__MANUAL__DOCUMENT_CREATION__SET_OBJECT_AND_TEMPLATE'  => 'Bitte wählen Sie eine Vorlage aus.',
    'LC__MANUAL__DOCUMENT_CREATION__ERROR_WHILE_COMPILE'      => "Während der Kompilierung ist ein Fehler aufgetreten. Detailierte Informationen können Sie dem i-doit Exception-Log entnehmen.",

    /* Placeholder */
    'LC__MANUAL__PLACEHOLDER__MAIN_OBJECT'                    => 'Haupt-Objekt',
    'LC__MANUAL__PLACEHOLDER__EXTERNAL_OBJECT'                => 'Externes Objekt',
    'LC__MANUAL__PLACEHOLDER__REPORT'                         => 'Report',
    'LC__MANUAL__PLACEHOLDER__TEMPLATE_VARS'                  => 'Template-Variablen',

    /* Category: Documents */
    'LC__CMDB__CATG__DOCUMENT'                                => 'Dokumente',
    'LC__MANUAL__CATEGORY_ENTRIES'                            => 'Kategorie-Einträge',
    'LC__CMDB__CATG__DOCUMENT__TEMPLATE_TITLE'                => 'Dokument-Titel',
    'LC__CMDB__CATG__DOCUMENT__TYPE_TITLE'                    => 'Kategorie',
    'LC__CMDB__CATG__DOCUMENT__CREATION_REFRESH_DATE'         => 'Erstellungs-/Aktualisierungsdatum',
    'LC__MANUAL__DOCUMENT__CREATION_EDIT'                     => 'Dokument erstellen/bearbeiten',
    'LC__MANUAL__DOCUMENT__COMPILED_TEXT'                     => 'Dokumentinhalt',

    /* Notifications */
    'LC__MANUAL__NOTIFICATION__DOCUMENT__SUCCESS'             => 'Dokument wurde erfolgreich gespeichert.',
    'LC__MANUAL__NOTIFICATION__TEMPLATE__SUCCESS'             => 'Vorlage wurde erfolgreich gespeichert.',
    'LC__MANUAL__NOTIFICATION__CHAPTER__SUCCESS'              => 'Kapitel wurde erfolgreich gespeichert.',
    'LC__MANUAL__NOTIFICATION__COPY__SUCCESS'                 => 'Kapitelinhalt wurde erfolgreich kopiert.',
    'LC__MANUAL__NOTIFICATION__COPY__SELECT_CHAPTER'          => 'Bitte wählen Sie ein Kapitel aus.',

    /* Rights */
    'LC__AUTH_GUI__DOCUMENTS'                                 => 'Dokument',
    'LC__AUTH_GUI__TEMPLATES'                                 => 'Vorlage',
    'LC__AUTH_GUI__CHAPTERS'                                  => 'Kapitel',

    /* Auth exceptions */
    'LC__AUTH__MANUAL_EXCEPTION__MISSING_RIGHT_FOR_TEMPLATES' => 'Sie besitzen nicht die notwendigen Rechte.',
    'LC__AUTH__MANUAL_EXCEPTION__MISSING_RIGHT_FOR_DOCUMENTS' => 'Sie besitzen nicht die notwendigen Rechte.',
    'LC__AUTH__MANUAL_EXCEPTION__MISSING_RIGHT_FOR_CHAPTERS'  => 'Sie besitzen nicht die notwendigen Rechte.',

    /* MISC */
    'LC__MANUAL__TABLE_OF_CONTENTS'                           => 'Inhaltsverzeichnis',
    'LC__MANUAL__PLACEHOLDER__ENTRIES_OF'                     => 'Einträge von ',
    'LC__MANUAL__PLACEHOLDER__CATEGORY_PROPERTY'              => 'Kategorie-Eigenschaft ',
    'LC__MANUAL__DOCUMENT__EXPORT_AS'                         => 'Herunterladen als',
    'LC__MANUAL__DOCUMENT__REGENERATE_DOCUMENT'               => 'Dokument aktualisieren',
    'LC__MANUAL__DOCUMENT_CREATION__REGENERATION_SUCCESS'     => 'Das Dokument wurde erfolgreich aktualisiert.',
    'LC__MANUAL__DOCUMENT_CREATION__REGENERATION_FAIL'        => 'Das Dokument konnte aufgrund eines Fehlers nicht aktualisiert werden: ',
    'LC__MODULE__MANUAL'                                      => 'Handbuch',
    'LC__MANUAL__DOCUMENT__DESCRIPTION'                       => 'Bemerkung',
    'LC__MANUAL__DOCUMENT__TYPE'                              => 'Kategorie',
    'LC__MANUAL__CONNECTED_OBJECT'                            => 'verknüpftes Objekt',
    'LC__MANUAL__NO_CONNECTED_OBJECT'                         => 'kein verknüpftes Objekt',
    'LC__MANUAL__DOCUMENT_TEMPLATE'                           => 'Dokument-Vorlage',
    'LC__MANUAL__NEW_TEMPLATE'                                => 'Neue Vorlage',
    'LC__MANUAL__NEW_CHAPTER'                                 => 'Neues Kapitel ',

    /* Errors */
    'LC__MANUAL__ERROR__EXPORT'                               => 'Beim Exportieren des Dokuments ist ein Fehler aufgetreten: ',
];

