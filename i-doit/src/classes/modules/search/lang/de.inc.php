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
 * "Search" Module language file
 *
 * @package
 * @subpackage     Language
 * @copyright      2016 synetics GmbH
 * @version        1.7.1
 * @license        http://www.i-doit.com/license
 */

return [
    'LC__SEARCH__CONFIG__MODE'              => 'Standard Such-Modus',
    'LC__SEARCH__CONFIG__NORMAL_DESCRIPTION' => '<strong>Normal</strong>: Normaler Suchmodus, findet teilweise Übereinstimmungen jedoch nur, wenn der Suchbegriff mit dem Fundbegriff <strong>beginnt</strong> (z.B. Suche nach "Micr Office" findet "Microsoft Office" aber nicht "Open Office")',
    'LC__SEARCH__CONFIG__FUZZY_DESCRIPTION' => '<strong>Fuzzy</strong>: Wie Normal, findet jedoch auch Teile mehrerer Suchbegriffe (z.B. Suche nach "Micr Office" findet "Microsoft Windows" und "Open Office")',
    'LC__SEARCH__CONFIG__DEEP_DESCRIPTION'  => '<strong>Deep</strong>: Alle Arten von teilweiser Übereinstimmung (z.B. "icrosoft" liefert "Microsoft") - Dadurch das Teilbegriffe nicht indiziert werden können ist diese Suche wesentlich rechenintensiver und langsamer',
    'LC__SEARCH__CONFIG__SUGGESTION_NOTE'   => 'Hinweis: Die Defaults funktionieren nicht in der Autovervollständigung'
];