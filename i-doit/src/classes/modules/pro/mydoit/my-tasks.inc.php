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
 * my-doit Task-Area.
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @version    0.9 Thu Jun 29 11:12:33 CEST 2006
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

// Prepare dao's.
$l_user_dao     = isys_component_dao_user::instance($g_comp_database);
$l_user_id      = $l_user_dao->get_current_user_id();
$l_workflow_dao = new isys_workflow_dao_action($g_comp_database);

// Get number of mytask entries from registry.
$l_max_entries = isys_tenantsettings::get('cmdb.registry.mytask_entries', 10);

// Accepted.
$l_tasks[0] = $l_workflow_dao->get_workflows(
    null,
    null,
    null,
    C__WORKFLOW__ACTION__TYPE__ACCEPT,
    " AND (isys_workflow__status = '" . C__TASK__STATUS__ACCEPT . "' OR isys_workflow__status = '" . C__TASK__STATUS__OPEN . "') " . "AND isys_workflow__isys_workflow_type__id != " . C__WORKFLOW_TYPE__CHECKLIST,
    $l_max_entries,
    null,
    null,
    "startdate DESC",
    $l_user_id,
    null
);

// Created.
$l_tasks[2] = $l_workflow_dao->get_workflows(
    null,
    null,
    null,
    C__WORKFLOW__ACTION__TYPE__NEW,
    "AND isys_workflow__isys_workflow_type__id != " . C__WORKFLOW_TYPE__CHECKLIST,
    $l_max_entries,
    null,
    null,
    "startdate DESC",
    $l_user_id,
    null
);

// Assigned.
$l_tasks[1] = $l_workflow_dao->get_workflows(
    null,
    null,
    null,
    C__WORKFLOW__ACTION__TYPE__ASSIGN,
    " AND (isys_workflow__status = '" . C__TASK__STATUS__INIT . "' OR isys_workflow__status = '" . C__TASK__STATUS__ASSIGNMENT . "') " . "AND isys_workflow__isys_workflow_type__id != " . C__WORKFLOW_TYPE__CHECKLIST,
    $l_max_entries,
    null,
    null,
    "startdate DESC",
    $l_user_id,
    null
);

// Iterate through menu entries currently : my- and assigned-tasks.
$l_added = [];
for ($i = 0;$i <= 2;$i++)
{
    while ($l_row = $l_tasks[$i]->get_row())
    {
        if ($l_row["isys_workflow__status"] < C__TASK__STATUS__END)
        {
            if (!array_key_exists($l_row["isys_workflow__id"], $l_added))
            {
                if (empty($l_row["startdate"]))
                {
                    $l_tmp  = $l_workflow_dao->get_workflows($l_row["isys_workflow__id"]);
                    $l_rtmp = $l_tmp->get_row();

                    $l_row["startdate"] = $l_rtmp["startdate"];
                } // if

                $l_ar_tasks[$i][] = [
                    "title" => $l_row["isys_workflow__title"],
                    "date"  => $g_loc->fmt_datetime($l_row["startdate"]),
                    "link"  => isys_helper_link::create_url(
                        [
                            C__CMDB__GET__VIEWMODE => C__WF__VIEW__DETAIL__GENERIC,
                            C__CMDB__GET__TREEMODE => C__WF__VIEW__TREE,
                            C__WF__GET__ID         => $l_row["isys_workflow__id"]
                        ]
                    )
                ];
            } // if

            $l_added[$l_row["isys_workflow__id"]] = true;
        } // if
    } // while
} // for

$g_comp_template->assign("g_tasks__accepted", $l_ar_tasks[0])
    ->assign("g_tasks__created", $l_ar_tasks[2])
    ->assign("g_tasks__assigned", $l_ar_tasks[1]);

unset($l_user_dao, $l_user_id, $l_task_dao, $l_tasks);
?>