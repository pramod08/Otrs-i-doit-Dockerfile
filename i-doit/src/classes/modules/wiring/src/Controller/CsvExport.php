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
namespace idoit\Module\Wiring\Controller;

use idoit\Module\Wiring\Model\Wiring;
use isys_controller as Controllable;
use League\Csv\Writer;

/**
 * i-doit cmdb controller.
 *
 * @package     i-doit
 * @subpackage  Wiring
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class CsvExport extends Main implements Controllable
{
    /**
     * Default request handler, gets called in every request.
     *
     * @param   \isys_register    $p_request
     * @param   \isys_application $p_application
     *
     * @return  \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        ;
    } // function

    /**
     * Default action.
     *
     * @param   \isys_register    $p_request
     * @param   \isys_application $p_application
     *
     * @return  \idoit\View\Renderable
     */
    public function onDefault(\isys_register $p_request, \isys_application $p_application)
    {
        try
        {
            // Check for view right first.
            \isys_auth_wiring::instance()
                ->wiring_object(\isys_auth::VIEW);

            // Retrieve posts from request.
            $posts = new \isys_array($p_request->get('POST'));

            // Object id set?
            $objID = $posts->get('cmdb_object__HIDDEN');

            if ($objID)
            {
                $csv = $this->exportCSV($objID, $posts['alignOutput'] ? json_decode($posts['alignOutput']) : $posts['alignOutput'], $p_application);
                $csv->output('wiring-export-' . $objID . '.csv');
            }
            else
            {
                throw new \InvalidArgumentException(_L('LC__CMDB__BROWSER_OBJECT__PLEASE_CHOOSE'));
            } // if

            die;
        }
        catch (\Exception $e)
        {
            \isys_application::instance()->container['notify']->error($e->getMessage());
        } // try
    } // function

    /**
     * Export action.
     *
     * @param   integer           $objID
     * @param   boolean           $alignOutput
     * @param   \isys_application $p_application
     *
     * @return  Writer
     */
    public function exportCSV($objID, $alignOutput = true, \isys_application $p_application)
    {
        // Get CSV Writer instance.
        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // Get wiring model to do the traverse.
        /* @var  $model  Wiring */
        $model = $this->dao($p_application);
        $model->setAlignCableRun($alignOutput);

        // Get all cable hops.
        $data    = $model->resolve($objID);
        $maxHops = $model->getMaxHops();

        // Prepare header.
        $header = [];

        for ($i = 0;$i <= $maxHops;$i++)
        {
            $header[] = 'Eingang';
            $header[] = 'Objekt';
            $header[] = 'Ausgang';
            $header[] = 'Anschluss';
            $header[] = 'Kabel';
        } // foreach

        $csv->insertOne($header);
        $lineNum = 0;

        foreach ($data as $cablerun)
        {
            $line = [];

            foreach ($cablerun as $hop)
            {
                $line[$lineNum][] = $hop['port'] ?: ' ';
                $line[$lineNum][] = $hop['object'] ?: ' ';
                $line[$lineNum][] = $hop['sibling'] ?: ' ';
                $line[$lineNum][] = $hop['type'] ?: ' ';
                $line[$lineNum][] = $hop['cable'] ?: ' ';
            } // foreach

            if (count($line))
            {
                $csv->insertOne($line[$lineNum]);
            } // if

            $lineNum++;
            unset($line);
        } // foreach

        return $csv;
    } // function
} // class