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
 * Smarty plugin for WYSIWYG input fields.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_smarty_plugin_f_wysiwyg extends isys_smarty_plugin_f_textarea implements isys_smarty_plugin
{
    protected static $m_toolbar_configuration = [
        'full'  => [
            [
                'name'  => 'clipboard',
                'items' => [
                    'Cut',
                    'Copy',
                    'Paste',
                    'PasteText',
                    'PasteFromWord',
                    '-',
                    'Undo',
                    'Redo'
                ]
            ],
            [
                'name'  => 'editing',
                'items' => [
                    'Find',
                    'Replace',
                    '-',
                    'SelectAll'
                ]
            ],
            [
                'name'  => 'links',
                'items' => [
                    'Link',
                    'Unlink',
                    'Anchor'
                ]
            ],
            [
                'name'  => 'insert',
                'items' => [
                    'Image',
                    'Table',
                    'HorizontalRule'
                ]
            ],
            [
                'name'  => 'tools',
                'items' => [
                    'Maximize',
                    'ShowBlocks'
                ]
            ],
            [
                'name'  => 'document',
                'items' => [
                    'Source',
                    '-',
                    'Print'
                ]
            ],
            '/',
            [
                'name'  => 'basicstyles',
                'items' => [
                    'Bold',
                    'Italic',
                    'Underline',
                    'Strike',
                    'Subscript',
                    'Superscript',
                    '-',
                    'RemoveFormat'
                ]
            ],
            [
                'name'  => 'paragraph',
                'items' => [
                    'NumberedList',
                    'BulletedList',
                    '-',
                    'Outdent',
                    'Indent',
                    '-',
                    'Blockquote',
                    '-',
                    'JustifyLeft',
                    'JustifyCenter',
                    'JustifyRight',
                    'JustifyBlock'
                ]
            ],
            [
                'name'  => 'styles',
                'items' => [
                    'Styles',
                    'Format',
                    'Font',
                    'FontSize'
                ]
            ],
            [
                'name'  => 'colors',
                'items' => [
                    'TextColor',
                    'BGColor'
                ]
            ]
        ],
        'basic' => [
            [
                'name'  => 'basicstyles',
                'items' => [
                    'Bold',
                    'Italic',
                    'Underline',
                    'Strike',
                    '-',
                    'RemoveFormat'
                ]
            ],
            [
                'name'  => 'script',
                'items' => [
                    'Subscript',
                    'Superscript'
                ]
            ],
            [
                'name'  => 'paragraph',
                'items' => [
                    'NumberedList',
                    'BulletedList'
                ]
            ],
            [
                'name'  => 'indent',
                'items' => [
                    'Outdent',
                    'Indent'
                ]
            ],
            [
                'name'  => 'UndoRedo',
                'items' => [
                    'Undo',
                    'Redo'
                ]
            ],
            [
                'name'  => 'tools',
                'items' => ['Maximize']
            ],
        ]
    ];
    /**
     * Whitelist of all allowed tags. "P", "BR" and "DIV" are allowed, because not all browsers use the same line delimiter.
     * Also IE Browsers work with "STRONG", "EM" and a mix of "P" + "DIV" for new lines... Weird.
     *
     * @var  array
     */
    protected static $m_whitelist_tags = [
        'b',
        'i',
        'u',
        'strike',
        'sub',
        'sup',
        'strong',
        'em',
        // Text formatting.
        'ol',
        'ul',
        'li',
        // Lists.
        'blockquote',
        // Special formatting.
        'hr',
        'br',
        // Breaks and lines.
        'div',
        'p',
        'span',
        // Container.
        'table',
        'thead',
        'tbody',
        'tr',
        'th',
        'td'
        // Tables.
    ];

    /**
     * This method will return the toolbar configurations.
     *
     * @param   string $p_config
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_toolbar_configuration($p_config = null)
    {
        if ($p_config !== null && isset(self::$m_toolbar_configuration[$p_config]))
        {
            return self::$m_toolbar_configuration[$p_config];
        } // if

        return self::$m_toolbar_configuration;
    } // function

    /**
     * This method returns the (default) whitelisted tags.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tags_whitelist()
    {
        return self::$m_whitelist_tags;
    } // function

    /**
     * This method adds additional tags to the whitelist
     *
     * @param $p_array
     */
    public static function add_tags_to_whitelist($p_array)
    {
        self::$m_whitelist_tags = array_merge(self::$m_whitelist_tags, $p_array);
    } // function

    /**
     * Edit mode - Parameters are given in an array $p_param:
     *     Basic parameters
     *         id                        -> ID
     *         name                      -> Name
     *         type                      -> Smarty plug in type
     *         p_strValue                -> Value
     *
     *     Style parameters
     *         p_strStyle                -> Style
     *         p_bEditMode               -> If set to 1 the plug in is always shown in edit style
     *         p_bDisabled               -> Disabled
     *         p_bReadonly               -> Readonly
     *
     *     Special parameters
     *         p_nRows                   -> Textarea rows
     *         p_nCols                   -> Textarea cols
     *         p_extraplugins            -> Extra-Plugins as comma-seperated list
     *         p_toolbarconfig           -> This parameter must be a valid array key of $m_toolbar_configuration. Otherwise all configured toolbars from ckeditor/config.js will be loaded.
     *         p_overwrite_toolbarconfig -> This parameter can be used to overwrite the default toolbar config (via "p_toolbarconfig").
     *         p_onblur                  -> This parameter contains a javascript callback function: Use 'this' and 'evt' as possible parameters for your callback.
     *         p_onready                 -> This parameter contains a javascript callback function: Use 'this' and 'evt' as possible parameters for your callback.
     *         p_onchange                -> This parameter contains a javascript callback function: Use 'this' and 'evt' as possible parameters for your callback.
     *         p_bClickDelegator         -> This parameter activates onClick attributes.
     *         p_bStrip                  -> This parameter indicates, whether p_value should be stripped or not.
     *
     * @global  array                   $g_dirs
     * @global  array                   $g_config
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function navigation_edit(isys_component_template &$p_tplclass, $p_param = null)
    {
        global $g_config, $g_comp_session;

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        if (isset($p_param['p_bEditMode']) && !$p_param['p_bEditMode'])
        {
            return $this->navigation_view($p_tplclass, $p_param);
        } // if

        if (isset($g_config['wysiwyg']) && $g_config['wysiwyg'] == false)
        {
            return parent::navigation_edit($p_tplclass, $p_param);
        } // if

        $this->m_strPluginClass = 'f_text';
        $this->m_strPluginName  = $p_param['name'];

        if ($p_param['p_bDisabled'] || $p_param['p_bReadonly'])
        {
            $this->navigation_view($p_tplclass, $p_param);
        } // if

        // Enable entities by default (&quot; instead of ").
        if (!isset($p_param['entities']))
        {
            $p_param['entities'] = true;
        } // if

        // Strip all not-allowed tags (via strip_tags) and then remove all their attributes.
        if ((!isset($p_param['p_bStrip']) && isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) || $p_param['p_bStrip'] == true)
        {
            $p_param['p_strValue'] = isys_helper_textformat::strip_html_attributes(strip_tags($p_param['p_strValue'], '<' . implode('><', self::$m_whitelist_tags) . '>'));
        } // if

        if (isset($p_param['id']) && !empty($p_param['id']))
        {
            $l_id = $p_param['id'];
        }
        else
        {
            $l_id = $p_param['name'];
        } // if

        if (isset($p_param['p_nRows']) && $p_param['p_nRows'] > 0)
        {
            $l_rows = $p_param['p_nRows'];
        }
        else
        {
            $l_rows = 5;
        } // if

        if (isset($p_param['p_nCols']) && $p_param['p_nCols'] > 0)
        {
            $l_cols = $p_param['p_nCols'];
        }
        else
        {
            $l_cols = 70;
        } // if

        // Toolbar.
        if (isys_settings::get('gui.wysiwyg-all-controls', false) && (!isset($p_param['p_overwrite_toolbarconfig']) || !$p_param['p_overwrite_toolbarconfig']))
        {
            // If we allow "full control" we simply display ALL functions.
            $l_toolbarconfiguration = isys_format_json::encode(self::$m_toolbar_configuration['full']);
        }
        else
        {
            if (isset($p_param['p_toolbarconfig']) && isset(self::$m_toolbar_configuration[$p_param['p_toolbarconfig']]))
            {
                $l_toolbarconfiguration = isys_format_json::encode(self::$m_toolbar_configuration[$p_param['p_toolbarconfig']]);
            }
            else if (isset($p_param['p_toolbarconfig']))
            {
                if (is_array($p_param['p_toolbarconfig']))
                {
                    // Let us convert array to json
                    $l_toolbarconfiguration = isys_format_json::encode($p_param['p_toolbarconfig']);
                }
                else
                {
                    $l_toolbarconfiguration = $p_param['p_toolbarconfig'];
                } // if
            }
            else
            {
                $l_toolbarconfiguration = isys_format_json::encode(self::$m_toolbar_configuration['basic']);
            } // if
        }

        $l_image_upload = $l_image_browser = '';

        if (isset($p_param['p_image_upload_handler']) && !empty($p_param['p_image_upload_handler']))
        {
            $l_image_upload = 'filebrowserUploadUrl:"' . isys_helper_link::create_url(
                    [
                        'call'           => 'file',
                        'func'           => 'upload_by_ckeditor',
                        'ajax'           => 1,
                        'upload_handler' => $p_param['p_image_upload_handler']
                    ]
                ) . '",';
        } // if

        if (isset($p_param['p_image_browser_handler']) && !empty($p_param['p_image_browser_handler']))
        {
            $l_image_browser = 'filebrowserBrowseUrl:"' . isys_helper_link::create_url(
                    [
                        'call'           => 'file',
                        'func'           => 'browse_by_ckeditor',
                        'ajax'           => 1,
                        'upload_handler' => $p_param['p_image_browser_handler']
                    ]
                ) . '",' . 'filebrowserWindowWidth: "730",' . 'filebrowserWindowHeight: "480",';
        } // if

        if (!isset($p_param['p_strWidth']))
        {
            $l_width = 'width:552px;';
        }
        else
        {
            $l_width = 'width:' . $p_param['p_strWidth'] . ';';
        }

        $l_style                       = $l_width . $p_param['p_strStyle'];
        $p_param['p_strInfoIconClass'] = 'fl mt5';

        if (!isset($p_param['p_strHeight']))
        {
            $l_height = '200px';
        }
        else
        {
            $l_height = $p_param['p_strHeight'];
        }

        // Add the given CSS classes behind our base "commentary" class.
        $p_param['p_strClass'] = 'commentary ' . $p_param['p_strClass'];

        // @todo  ID-1365 before removing "inputTextarea" check if any modules use this class to identify this element.
        return $this->getInfoIcon($p_param) . '<div class="' . $p_param['p_strClass'] . '">
			<textarea class="inputTextarea" style="' . $l_style . '" ' . 'data-identifier="' . $p_param['p_dataIdentifier'] . '" id="' . $l_id . '" name="' . $p_param['name'] . '" rows="' . $l_rows . '" cols="' . $l_cols . '">' . // This is necessary to prevent strings like "<mytag>" to be turned into HTML.
        str_replace('&', '&amp;', $p_param['p_strValue']) . '</textarea></div>
			<script type="text/javascript">
				var ' . $l_id . ' = CKEDITOR.replace( "' . $l_id . '", {
	                    extraPlugins: "' . $p_param['p_extraplugins'] . ',widget",
	                    language: "' . $g_comp_session->get_language() . '",
	                    allowedContent: true,
	                    toolbar : ' . $l_toolbarconfiguration . ',
	                    removeButtons: "",
	                    height: "' . $l_height . '",
	                    removePlugins: "div,flash,smiley,specialchar,forms,pagebreak,iframe,about",
	                    font_names: "' . isys_tenantsettings::get('ckeditor.font_names', 'Arial;Courier New;Times New Roman;Helvetica') . '",
	                    readOnly: ' . (isset($p_param['p_bReadonly']) && $p_param['p_bReadonly'] ? 'true' : 'false') . ',
						entities: ' . ($p_param['entities'] ? 'true' : 'false') . ',
						' . $l_image_upload . '
						' . $l_image_browser . '
	                    on: {
	                        instanceReady: function (evt) {
	                            /* Custom callback */
	                            ' . $p_param['p_onready'] . '
	                        },
	                        ' . ($p_param['p_bClickDelegator'] ? 'contentDom: function(evt) {
					var editable = this.editable();

					editable.attachListener(editable, "click", function(evt) {
						if (evt.data.$.hasOwnProperty("srcElement") && evt.data.$.srcElement.getAttribute("data-cke-pa-onclick")) {
							evt.data.$.srcElement.setAttribute("onclick", evt.data.$.srcElement.getAttribute("data-cke-pa-onclick"));
							evt.data.$.srcElement.onclick.apply(evt.data.$.srcElement);
						}
					});

					editable.attachListener(editable, "dblclick", function(evt) {
						if (evt.data.$.hasOwnProperty("srcElement") && evt.data.$.srcElement.getAttribute("data-cke-pa-ondblclick")) {
							evt.data.$.srcElement.setAttribute("ondblclick", evt.data.$.srcElement.getAttribute("data-cke-pa-ondblclick"));
							evt.data.$.srcElement.ondblclick.apply(evt.data.$.srcElement);
						}
					});
				},' : '') . '
	                        blur: function(evt) {
	                            // Blur action.
	                            ' . $p_param['p_onblur'] . '

								// Trigger the textarea\'s "onChange" event.' . ($l_id ? 'if ($("' . $l_id . '")) $("' . $l_id . '").simulate("change");' : '') . '
	                        },
	                        change: function(evt) {
	                            // @todo Unlike "onChange" this event gets called by every new character input. Check, if this is necessary

	                            /* Sync content with textarea */
	                            this.updateElement();

	                            // Change action.
	                            ' . $p_param['p_onchange'] . '
	                        }
	                    }
	            });
			</script>';
    } // function

    /**
     * View mode.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_param = null)
    {
        global $g_config;

        if ($p_param === null)
        {
            $p_param = $this->m_parameter;
        } // if

        if (isset($g_config['wysiwyg']) && $g_config['wysiwyg'] == false)
        {
            return parent::navigation_view($p_tplclass, $p_param);
        } // if

        if (isset($p_param['p_bEditMode']) && $p_param['p_bEditMode'])
        {
            return $this->navigation_edit($p_tplclass, $p_param);
        } // if

        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1))
        {
            // Strip all not-allowed tags (via strip_tags) and then remove all their attributes.
            $p_param['p_strValue'] = isys_helper_textformat::strip_html_attributes(strip_tags($p_param['p_strValue'], '<' . implode('><', self::$m_whitelist_tags) . '>'));
        } // if

        // After stripping all "evil" we can replace the links and email addresses.
        $p_param['p_strValue'] = isys_helper_textformat::link_urls_in_string($p_param['p_strValue']);
        $p_param['p_strValue'] = isys_helper_textformat::link_mailtos_in_string($p_param['p_strValue']);

        return '<div class="commentary wysiwyg">' . $p_param['p_strValue'] . '</div>';
    } // function
} // class