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
namespace idoit\Module\Wiring\View;

use idoit\Model\Breadcrumb;
use idoit\Model\Dao\Base as DaoBase;
use idoit\Module\Wiring\Model\Wiring;
use idoit\View\Base;
use idoit\View\Renderable;
use isys_component_template as ComponentTemplate;
use isys_module as ModuleBase;

/**
 * i-doit cmdb controller.
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Object extends Base implements Renderable
{
    /**
     * Process method.
     *
     * @param   ModuleBase        $p_module
     * @param   ComponentTemplate $p_template
     * @param   DaoBase|Wiring    $p_model
     *
     * @return  $this
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {
        // Set breadcrumb.
        $p_module->set(
            'breadcrumb',
            [
                new Breadcrumb(_L('LC__MODULE__WIRING__OBJECT'), C__MODULE__WIRING)
            ]
        );

        $connectorTypesForDialog = [];
        $connectorTypes          = $p_model->getConnectionTypes();

        foreach ($connectorTypes as $id => $connector)
        {
            $connectorTypesForDialog[$id] = $connector['title'];
        } // foraech

        // Assign connection types for a legend.
        $p_template->activate_editmode()
            ->assign('types', $connectorTypes)
            ->smarty_tom_add_rules(
                'tom.content.bottom.content',
                [
                    'cmdb_object'                  => [
                        'p_strValue'                                       => $this->request->get('id'),
                        \isys_popup_browser_object_ng::C__CALLBACK__ACCEPT => "$('cmdb_object__VIEW').fire('updated:selection');",
                        \isys_popup_browser_object_ng::C__CALLBACK__DETACH => "$('cmdb_object__VIEW').fire('updated:selection');"
                    ],
                    'wiring_connector_types'       => [
                        'p_arData'     => $connectorTypesForDialog,
                        'p_strClass'   => 'input-mini ml10',
                        'p_bDbFieldNN' => true,
                        'p_bDisabled'  => true
                    ],
                    'wiring_connector_save_button' => [
                        'icon'        => \isys_application::instance()->www_path . 'images/icons/silk/disk.png',
                        'p_strValue'  => 'LC__MODULE__WIRING__UPDATE_CONNECTORS',
                        'p_strClass'  => 'ml10',
                        'p_bDisabled' => true
                    ]
                ]
            );

        // Set paths to templates.
        $this->paths['contentbottomcontent'] = $p_module->get_template_dir() . 'views/wiring-object.tpl';

        return $this;
    } // function
} // class