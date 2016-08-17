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
 * Smarty plugin for the title suffix counter.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Van Quyen Hoang<qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_title_suffix_counter extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoangq@i-doit.org>
     */
    public static function get_meta_map()
    {
        return [];
    } // function

    /**
     * Method for view-mode.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        // If we force the edit mode, display it.
        if (isys_glob_is_edit_mode() || $p_param['p_bEditMode'])
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        return null;
    } // function

    /**
     * Generates the titles for the plugin
     *
     * @static
     *
     * @param   array   $p_posts
     * @param   integer $p_suffix_ident
     * @param   integer $p_title_tag
     * @param   integer $p_position
     *
     * @return  array|string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @todo    Make more placeholders: ##COUNT##, ##ABC##, ##abc##, ##ROMAN## (I, II, ... VI, ...).
     */
    public static function generate_title_as_array($p_posts, $p_suffix_ident, $p_title_tag, $p_position = null)
    {
        $l_start_with = $p_posts[$p_suffix_ident . "__SUFFIX_COUNT_STARTING_AT"];

        if ($l_start_with < 0)
        {
            $l_start_with = 1;
        } // if

        $l_title = [];

        for ($i = $l_start_with;$i <= $p_posts[$p_suffix_ident . "__SUFFIX_COUNT"] + $l_start_with - 1;$i++)
        {
            if ($p_posts[$p_suffix_ident . "__SUFFIX_COUNT"] > 1)
            {
                if ($p_posts[$p_suffix_ident . "__SUFFIX_SUFFIX_TYPE"] != '')
                {
                    $l_counter = "";
                    if ($p_posts[$p_suffix_ident . "__SUFFIX_ZERO_POINT_CALC"] && $p_posts[$p_suffix_ident . "__SUFFIX_ZERO_POINT_CALC"] > 0)
                    {
                        for ($n = strlen(strval($i));$n <= $p_posts[$p_suffix_ident . "__SUFFIX_ZERO_POINTS"];$n++)
                        {
                            $l_counter .= "0";
                        } // for
                    } // if

                    $l_counter .= $i;

                    if ($p_posts[$p_suffix_ident . "__SUFFIX_SUFFIX_TYPE"] == -1)
                    {
                        $l_title[] = $p_posts[$p_title_tag] . str_replace('##COUNT##', $l_counter, $p_posts[$p_suffix_ident . "__SUFFIX_SUFFIX_TYPE_OWN"]);
                    }
                    else
                    {
                        $l_title[] = $p_posts[$p_title_tag] . $l_counter;
                    }
                }
                else
                {
                    $l_title[] = $p_posts[$p_title_tag];
                }
            }
            else
            {
                $l_title[] = $p_posts[$p_title_tag];
            } // if
        } // for

        if (!is_null($p_position))
        {
            return $l_title[$p_position];
        }
        else
        {
            return $l_title;
        } // if
    }

    /**
     * Method for edit-mode.
     *
     * List of usable parameters for $p_param:
     *
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_param
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        global $g_dirs;

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        // If edit mode is inactive, we call the navigation view.
        if (!isys_glob_is_edit_mode() && !$p_param['p_bEditMode'])
        {
            return $this->navigation_view($p_tplclass, $p_param);
        } // if

        if (!isset($p_param["title_identifier"]))
        {
            return false;
        }

        // Set a default name, when no name was given.
        if (!isset($p_param["name"]))
        {
            $p_param["name"] = 'default';
        } // if

        if (!isset($p_param["counter_name"]))
        {
            $p_param["counter_name"] = $p_param["name"] . '_COUNT';
        }

        if (!isset($p_param["suffix_type_identifier"]))
        {
            $p_param["suffix_type_identifier"] = $p_param["name"] . '_SUFFIX_TYPE';
        }

        if (!isset($p_param["suffix_type_own_identifier"]))
        {
            $p_param["suffix_type_own_identifier"] = $p_param["name"] . '_SUFFIX_TYPE_OWN';
        }

        if (!isset($p_param["count_start_identifier"]))
        {
            $p_param["count_start_identifier"] = $p_param["name"] . '_COUNT_STARTING_AT';
        }

        if (!isset($p_param["zero_point_calc_indentifier"]))
        {
            $p_param["zero_point_calc_indentifier"] = $p_param["name"] . '_ZERO_POINT_CALC';
        }

        if (!isset($p_param["zero_points_indentifier"]))
        {
            $p_param["zero_points_indentifier"] = $p_param["name"] . '_ZERO_POINTS';
        }

        if (!isset($p_param["label_counter"]))
        {
            $p_param["label_counter"] = '';
        }

        $l_plugin_count    = new isys_smarty_plugin_f_count();
        $l_plugin_text     = new isys_smarty_plugin_f_text();
        $l_plugin_checkbox = new isys_smarty_plugin_checkbox();
        $l_plugin_textarea = new isys_smarty_plugin_f_textarea();
        $l_plugin_label    = new isys_smarty_plugin_f_label();

        // Labels
        $l_label_counter = $l_plugin_label->navigation_view(
            $p_tplclass,
            [
                'name'  => $p_param["counter_name"],
                'ident' => $p_param["label_counter"]
            ]
        );

        $l_label_suffix_type = $l_plugin_label->navigation_view(
            $p_tplclass,
            [
                'name'  => $p_param["suffix_type_identifier"],
                'ident' => 'LC__UNIVERSAL__TITLE_SUFFIX'
            ]
        );

        $l_label_preview = $l_plugin_label->navigation_view(
            $p_tplclass,
            [
                'name'  => 'preview',
                'ident' => 'LC__UNIVERSAL__PREVIEW'
            ]
        );

        // Counter field.
        $l_counter = $l_plugin_count->navigation_edit(
            $p_tplclass,
            [
                'name'       => $p_param["counter_name"],
                'p_strStyle' => 'width:50px;',
                'p_onChange' => 'if (!isNaN($(\'' . $p_param["counter_name"] . '\').value) && $(\'' . $p_param["counter_name"] . '\').value > 1) {
				$$(\'.suf\').each(function(e){e.appear({duration:0.4});});
				$(\'' . $p_param["zero_points_indentifier"] . '\').value = $(\'' . $p_param["counter_name"] . '\').value.length;
				show_preview();
			} else {
				$$(\'.suf\').each(function(e){e.hide();});
			}'
            ]
        );

        // Title suffix type field.
        $l_title_suffix_own = $l_plugin_text->navigation_edit(
            $p_tplclass,
            [
                'p_bInfoIconSpacer' => '0',
                'p_bEditMode'       => '1',
                'name'              => $p_param["suffix_type_own_identifier"],
                'id'                => $p_param["suffix_type_own_identifier"],
                'p_strValue'        => '##COUNT##',
                'p_onChange'        => 'show_preview();',
                'p_strStyle'        => 'display:none;',
                'p_strClass'        => 'input input-small'
            ]
        );

        // Start count at field.
        $l_start_count = $l_plugin_text->navigation_edit(
            $p_tplclass,
            [
                'p_bInfoIconSpacer' => '1',
                'p_bEditMode'       => '1',
                'p_strClass'        => 'small',
                'name'              => $p_param["count_start_identifier"],
                'id'                => $p_param["count_start_identifier"],
                'p_onChange'        => "show_preview();",
                'p_strValue'        => '1',
            ]
        );

        // Checkbox whether to add zeros before the counter.
        $l_zero_point_calc = $l_plugin_checkbox->navigation_edit(
            $p_tplclass,
            [
                'name'              => $p_param["zero_point_calc_indentifier"],
                'id'                => $p_param["zero_point_calc_indentifier"],
                'p_bChecked'        => '1',
                'p_strValue'        => '1',
                'p_bInfoIconSpacer' => '0',
                'p_strOnClick'      => 'show_preview();'
            ]
        );

        // Field how many zeros will be added.
        $l_zero_points = $l_plugin_text->navigation_edit(
            $p_tplclass,
            [
                'p_bInfoIconSpacer' => '1',
                'p_bEditMode'       => '1',
                'p_strClass'        => 'small',
                'name'              => $p_param["zero_points_indentifier"],
                'id'                => $p_param["zero_points_indentifier"],
                'p_strValue'        => '2',
                'p_onChange'        => 'show_preview();'
            ]
        );

        // Preview field.
        $l_preview = $l_plugin_textarea->navigation_edit(
            $p_tplclass,
            [
                'p_nRows'           => '2',
                'p_bInfoIconSpacer' => '1',
                'p_bReadonly'       => '1',
                'p_bDisabled'       => '1',
                'p_bEditMode'       => '1',
                'name'              => 'preview',
                'id'                => 'preview',
                'p_strClass'        => 'noresize normal'
            ]
        );

        // Output
        $l_html = "<tr>
				<td class=\"key\">" . $l_label_counter . "</td>
				<td class=\"value\">" . $l_counter . "</td>
			</tr>
			<tr style=\"display:none\" class=\"suf\">
				<td style=\"vertical-align:top;\" class=\"key\">" . $l_label_suffix_type . ":</td>
				<td class=\"value\">
				<label class=\"ml20\"><input type=\"radio\" name=\"" . $p_param["suffix_type_identifier"] . "\" value=\"\" checked=\"checked\" onclick=\"show_preview();\" /> " . _L(
                "LC__UNIVERSAL__NO_SUFFIX"
            ) . "</label><br />
				<label class=\"ml20\"><input type=\"radio\" name=\"" . $p_param["suffix_type_identifier"] . "\" value=\"##COUNT##\" onclick=\"show_preview();\"/> \"" . _L(
                "LC__TEMPLATES__OBJECT_COUNTER"
            ) . "\"</label><br />
				<label class=\"ml20 mr10\"><input type=\"radio\" name=\"" . $p_param["suffix_type_identifier"] . "\" value=\"-1\" onclick=\"show_preview();\" onchange=\"if (this.checked) { $('" . $p_param["suffix_type_own_identifier"] . "').show(); }\" /> " . _L(
                "LC__TEMPLATES__OWN"
            ) . "</label>
				" . $l_title_suffix_own . "
				</td>
			</tr>
			<tr style=\"display:none\" class=\"suf\">
				<td style=\"vertical-align:top;\" class=\"key\"><label for=\"" . $p_param["count_start_identifier"] . "\">" . _L("LC__WORKFLOWS__STARTING_NOW") . ":</label></td>
				<td class=\"value\">
					" . $l_start_count . "
				</td>
			</tr>
			<tr style=\"display:none\" class=\"suf\">
				<td style=\"vertical-align:top;\" class=\"key\"><label for=\"" . $p_param["zero_point_calc_indentifier"] . "\">" . _L("LC__UNIVERSAL__LEADING_ZEROS") . ":</label></td>
				<td class=\"value\">
					" . $l_zero_points . "
					<label>" . $l_zero_point_calc . "" . _L('LC__NOTIFICATIONS__STATUS__ACTIVATED') . "?</label>
				</td>
			</tr>
			<tr style=\"display:none;\" class=\"suf\">
				<td style=\"vertical-align:top;\" class=\"key\">
					" . $l_label_preview . "
				</td>
				<td class=\"value\">
					" . $l_preview . "<br />
					<button type=\"button\" class=\"btn btn-small ml20 mt5\" onclick=\"show_preview();\"><img src=\"" . $g_dirs['images'] . "icons/silk/arrow_refresh.png\" class=\"mr5\" /><span>" . _L(
                'LC__UNIVERSAL__REFRESH_PREVIEW'
            ) . "</span></button>
				</td>
			</tr>";

        $l_jscript = "<script>
						window.show_preview = function(){
							var type = '';
							var additional = '';
							var ele = $('" . $p_param["title_identifier"] . "');
							$$('input[name=" . $p_param["suffix_type_identifier"] . "]:checked').find(function(e){
								type = e.value;
							});
							var start_with = parseInt($('" . $p_param["count_start_identifier"] . "').value),
								start_with_as_string = $('" . $p_param["count_start_identifier"] . "').value,
								zero_calc = $('" . $p_param["zero_point_calc_indentifier"] . "').checked,
								zero_points = parseInt($('" . $p_param["zero_points_indentifier"] . "').value),
								appending_zeros = '',
								appending = $('" . $p_param["suffix_type_own_identifier"] . "').value,
								preview = []

							for(i = 0; i < 3; i++){
								appending_zeros = '';
								additional = '';
								if(type != '') {
									if(zero_calc) {
										for(n = 0; n < zero_points; n++){
											appending_zeros += 0;
										}

										if(start_with > 9){
											appending_zeros = appending_zeros.substr(0, (appending_zeros.length - (start_with_as_string.length - 1)));
										}

										additional = appending_zeros;
									}
								}
								switch(type){
									case '##COUNT##':
										additional = additional + start_with;
										start_with = start_with + 1;
										start_with_as_string = String(start_with);
										break;
									case '-1':
										additional = appending.replace('##COUNT##', additional + start_with);
										start_with = start_with + 1;
										start_with_as_string = String(start_with);
										break;
									default:
										additional = \"\";
										break;
								}
								preview.push($(ele).value + additional);
							}

							$('preview').value = preview.join(" . '"\n"' . ");
						}

						document.observe('dom:loaded', function(){
							$('" . $p_param["title_identifier"] . "').on('change', function(){
								if(parseInt($('" . $p_param["counter_name"] . "').value) > 1){
									show_preview();
								}
							});
						});

						</script>";

        return $l_html . $l_jscript;

    } // function
} // class