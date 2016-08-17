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
    'LC__SEARCH__CONFIG__MODE'             => 'Default search mode',
    'LC__SEARCH__CONFIG__NORMAL_DESCRIPTION' => '<strong>Normal</strong>: Normal search, partial matching works only from the <strong>beginning</strong> of a keyword. (e.g. a search for "Micr Office" delivers "Microsoft Windows")',
    'LC__SEARCH__CONFIG__FUZZY_DESCRIPTION' => '<strong>Fuzzy</strong>: Like normal, but also finds parts of multiple search keywords. (e.g. a search for "Micr Office" delivers "Microsoft Windows" and "Open Office")',
    'LC__SEARCH__CONFIG__DEEP_DESCRIPTION'  => '<strong>Deep</strong>All kinds of martial matchings (e.g. "icrosoft" finds "Microsoft") - Since partial strings won\'t get indexed this search option is ways more cpu intensive and slower.',
    'LC__SEARCH__CONFIG__SUGGESTION_NOTE'   => 'Please note: Defaults do not work in auto suggestion'
];