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
 * Calendar class
 *
 *
 * @package     i-doit
 * @subpackage  popups
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_calendar extends isys_component_popup
{
    /**
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        $l_language = isys_component_session::instance()->get_language();

        if (!$l_language)
        {
            $l_language = "en";
        } // if

        $l_name        = str_replace(['[', ']', '-'], ['_', '_', '_'], $p_params["name"]);
        $l_hidden_date = '';

        if (strstr($p_params["name"], '[') && strstr($p_params["name"], ']'))
        {
            $l_tmp = explode('[', $p_params["name"]);

            $l_view   = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        }
        else
        {
            $l_view   = $p_params["name"] . '__VIEW';
            $l_hidden = $p_params["name"] . '__HIDDEN';
        } // if

        $l_readonly = false;

        if (isset($p_params['p_bReadonly']))
        {
            $l_readonly = filter_var($p_params['p_bReadonly'], FILTER_VALIDATE_BOOLEAN);
        } // if

        /**
         * DATE and TIME
         */
        if (isset($p_params["p_bTime"]) && $p_params["p_bTime"] == "1")
        {
            if ($p_params["p_strValue"])
            {
                $l_time_value = date("H:i:s", strtotime($p_params["p_strValue"]));
            }
            else
            {
                $l_time_value = "00:00:00";
            } // if

            if ($p_tplclass->editmode() || $p_params["p_bEditMode"] == true)
            {
                $l_attr_readonly = '';
                if ($l_readonly === true)
                {
                    $l_attr_readonly = ' readonly="readonly"';
                } //if

                $l_time = '<input type="text" class="input input-mini ml5" id="' . $l_name . '__TIME" name="' . $l_name . '__TIME" value="' . $l_time_value . '" ' . $l_attr_readonly . '/>';
            }
            else
            {
                $l_time = ' - ' . $l_time_value;
            } // if

            if ($p_params["p_strValue"])
            {
                $l_hidden_date          = date("Y-m-d H:i:s", strtotime($p_params["p_strValue"]));
                $p_params["p_strValue"] = isys_locale::get_instance()->fmt_date($p_params["p_strValue"]);
            } // if

            /**
             * DATE
             */
        }
        else
        {
            $l_time = "";

            if ($p_params["p_strValue"])
            {
                $l_hidden_date          = date("Y-m-d", strtotime($p_params["p_strValue"]));
                $p_params["p_strValue"] = isys_locale::get_instance()->fmt_date($p_params["p_strValue"]);
            }
        }
        if (!isset($p_params["showEffect"]) || !is_numeric($p_params["showEffect"]))
        {
            $p_params["showEffect"] = "slide";
        }

        if (!isset($p_params["closeEffect"]) || !is_numeric($p_params["closeEffect"]))
        {
            $p_params["closeEffect"] = "fade";
        }

        if (!isset($p_params["topOffset"]) || !is_numeric($p_params["topOffset"]))
        {
            $p_params["topOffset"] = "20";
        }

        if (!isset($p_params["enableYearBrowse"]))
        {
            $p_params["enableYearBrowse"] = "1";
        }
        else if ($p_params["enableYearBrowse"] == "0" || $p_params["enableYearBrowse"] == false)
        {
            $p_params["enableYearBrowse"] = "0";
        }

        if (isset($p_params["disableFutureDate"]))
        {
            $p_params["disableFutureDate"] = "true";
        }
        else
        {
            $p_params["disableFutureDate"] = "false";
        }

        if (isset($p_params["disablePastDate"]))
        {
            $p_params["disablePastDate"] = "true";
        }
        else
        {
            $p_params["disablePastDate"] = "false";
        }

        if (!isset($p_params["enableCloseOnBlur"]) || !$p_params["enableCloseOnBlur"])
        {
            $p_params["enableCloseOnBlur"] = "false";
        }
        else if ($p_params["enableCloseOnBlur"] == "1" || $p_params["enableCloseOnBlur"] == true)
        {
            $p_params["enableCloseOnBlur"] = "true";
        }

        if (isset($p_params['cellCallback']))
        {
            $l_cellCallback = ',cellCallback : ' . $p_params['cellCallback'];
        }
        else
        {
            $l_cellCallback = '';
        }

        if (isset($p_params['clickCallback']))
        {
            $l_clickCallback = ',clickCallback: ' . $p_params['clickCallback'];
        }
        else
        {
            $l_clickCallback = '';
        }

        $p_params["p_strID"] = $l_view;

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if ($p_tplclass->editmode() || $p_params["p_bEditMode"] == true)
        {
            if (isset($p_params["p_onChange"]))
            {
                $p_params["p_onChange"] = rtrim($p_params["p_onChange"], ';') . ';';
            } // if

            $l_raw_date_format = isys_locale::get_instance()->get_date_format();
            $l_date_splitter   = (strpos($l_raw_date_format, '.') ? '.' : '-');
            $l_date_format     = explode(
                $l_date_splitter,
                str_replace(['d', 'm', 'Y'], ['dd', 'mm', 'yyyy'], $l_raw_date_format)
            );
            $l_new_date_format = isys_format_json::encode($l_date_format);

            if ($l_readonly === false)
            {
                // @see ID-1904  Changed the DatePickerFormatter according to lines below.
                $p_params["p_onChange"] .= "var val = ''; if(! this.value.blank()) { var df = new DatePickerFormatter(" . str_replace(
                        '"',
                        "'",
                        $l_new_date_format
                    ) . ", '" . $l_date_splitter . "').match(this.value); val = df[0] + '-' + df[1] + '-' + df[2];} $('" . $l_hidden . "').setValue(val);";
            } //if

            $l_strHiddenField = '<input name="' . $l_hidden . '" id="' . $l_hidden . '" type="hidden" value="' . $l_hidden_date . '" />';

            $l_strOut = $l_objPlugin->navigation_edit($p_tplclass, $p_params) . $l_time . $l_strHiddenField;

            $p_params["closeOnBlurDelay"] = 15;

            if ($l_readonly === false)
            {
                $l_strOut .= "<script type=\"text/javascript\">" . "var dpck_" . $l_name . "	= new DatePicker({
  relative	: '" . $l_view . "',
  hidden	: '" . $l_hidden . "',
  time		: '" . $l_name . "__TIME',
  language	: '" . $l_language . "',
  closeEffect	: '" . $p_params["closeEffect"] . "',
  showEffect	: '" . $p_params["showEffect"] . "',
  keepFieldEmpty : true,
  disableFutureDate: " . $p_params["disableFutureDate"] . ",
  disablePastDate: " . $p_params["disablePastDate"] . ",
  topOffset : " . $p_params["topOffset"] . ",
  enableYearBrowse : " . $p_params["enableYearBrowse"] . ",
  enableCloseOnBlur : " . $p_params["enableCloseOnBlur"] . ",
  closeOnBlurDelay: " . $p_params["closeOnBlurDelay"] . ",
  wrongFormatMessage: '" . _L('LC_CALENDAR_POPUP__WRONGDATE') . "',
  zindex : 99999
  " . $l_cellCallback . $l_clickCallback . "
});
dpck_" . $l_name . ".setDateFormat(" . $l_new_date_format . ", \"" . $l_date_splitter . "\");
dpck_" . $l_name . ".setHiddenFormat([ \"yyyy\", \"mm\", \"dd\" ], \"-\");

Event.observe(window, 'load', function() {
	delete dpck_" . $l_name . ";
	var dpck_" . $l_name . "	= new DatePicker({
		relative	: '" . $l_view . "',
		hidden		: '" . $l_hidden . "',
		time		: '" . $l_name . "__TIME',
		language	: '" . $l_language . "',
		closeEffect	: '" . $p_params["closeEffect"] . "',
		showEffect	: '" . $p_params["showEffect"] . "',
		keepFieldEmpty : true,
		disableFutureDate: " . $p_params["disableFutureDate"] . ",
		disablePastDate: " . $p_params["disablePastDate"] . ",
		topOffset : " . $p_params["topOffset"] . ",
		enableYearBrowse : " . $p_params["enableYearBrowse"] . ",
		enableCloseOnBlur : " . $p_params["enableCloseOnBlur"] . ",
		closeOnBlurDelay: " . $p_params["closeOnBlurDelay"] . ",
		wrongFormatMessage: '" . _L('LC_CALENDAR_POPUP__WRONGDATE') . "',
		zindex : 99999
		" . $l_cellCallback . $l_clickCallback . "
	 });
	 dpck_" . $l_name . ".setDateFormat(" . $l_new_date_format . ", \"" . $l_date_splitter . "\");
	 dpck_" . $l_name . ".setHiddenFormat([ \"yyyy\", \"mm\", \"dd\" ], \"-\");
});" .

                    "</script>";
            } //if

        }
        else
        {
            $l_strOut = $l_objPlugin->navigation_view($p_tplclass, $p_params);
            $l_strOut .= $l_time;
        }

        return $l_strOut;
    } // function

    /**
     * @deprecated
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  null
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        return null;
    } // function
} // class