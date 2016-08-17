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
 * Notification: License has expired.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_notification_certificate_expiration extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     */
    protected function handle_notification($p_notification)
    {
        // Check threshold and its unit:
        $l_cmdb_dao = new isys_cmdb_dao_category_g_certificate($this->m_db);
        if (!isset($p_notification['threshold']))
        {
            $this->m_log->warning(
                'Threshold is not set! Skip notification.'
            );

            return $this->mark_notification_as_incomplete($p_notification);
        } //if

        if (!isset($p_notification['threshold_unit']))
        {
            $this->m_log->warning(
                'Threshold unit is not set! Skip notification.'
            );

            return $this->mark_notification_as_incomplete($p_notification);
        } //if

        // Fetch objects selected by notification:
        $l_notification_objects = $this->m_dao->get_objects($p_notification['id']);

        // Get objects of type license:

        $l_objects = [];

        foreach ($l_notification_objects as $l_object)
        {
            $l_res = $l_cmdb_dao->get_all_catg_by_obj_type_id($l_object['isys_obj_type__id'], C__CATG__CERTIFICATE);
            if ($l_res->num_rows())
            {
                $l_objects[] = $l_object;
            } //if
        } //foreach

        unset ($l_notification_objects);

        $l_num = count($l_objects);

        if ($l_num == 0)
        {
            $this->m_log->warning(
                'No Certificates have been set to report! Skip notification.'
            );

            return $this->mark_notification_as_incomplete($p_notification);
        }
        else
        {
            $this->m_log->info(
                sprintf(
                    'Amount of Certificates: %s',
                    $l_num
                )
            );
        } //if

        // Check whether license is expired:
        $l_licenses = [];

        $l_now       = time();
        $l_threshold = null;

        $l_unit                   = $this->m_dao->get_unit($this->m_type['unit']);
        $l_unit_parameters        = $this->m_dao->get_unit_parameters($l_unit['table']);
        $l_notification_threshold = $p_notification['threshold'];

        // Get the right unit parameter:
        foreach ($l_unit_parameters AS $l_parameter)
        {
            if ($l_parameter[$l_unit['table'] . '__id'] == $p_notification['threshold_unit'])
            {
                $l_day   = (int) date('d', $l_now);
                $l_month = (int) date('m', $l_now);
                $l_year  = (int) date('Y', $l_now);

                switch ($l_parameter[$l_unit['table'] . '__const'])
                {
                    case 'C__CMDB__UNIT_OF_TIME__MONTH':

                        while ($l_month < $l_notification_threshold)
                        {
                            $l_month += 12;
                            $l_year--;
                        }
                        $l_month -= $l_notification_threshold;
                        $l_days_in_month = date('t', strtotime($l_year . '-' . $l_month . '-01'));
                        if ($l_day > $l_days_in_month)
                        {
                            $l_day = $l_days_in_month;
                        } // if
                        $l_threshold = $l_now - strtotime($l_year . '-' . $l_month . '-' . $l_day);

                        break;
                    case 'C__CMDB__UNIT_OF_TIME__YEAR':
                        $l_year      = $l_year - $l_notification_threshold;
                        $l_threshold = $l_now - strtotime($l_year . '-' . $l_month . '-' . $l_day);
                        break;
                    default:
                        $l_threshold = $l_parameter[$l_unit['table'] . '__value'] * $l_notification_threshold;
                        break;
                }
                break;
            } // if
        } // foreach

        if ($l_threshold === null)
        {
            $this->m_log->warning(
                'Threshold unit is not set! Skip notification.'
            );

            return $this->mark_notification_as_incomplete($p_notification);
        } // if

        // Iterate through each license:
        foreach ($l_objects as $l_object)
        {
            $this->m_log->info(
                sprintf(
                    'Handling Object "%s"...',
                    $l_object['isys_obj__title']
                )
            );

            // Fetch category data from database:
            $l_cat_data = $l_cmdb_dao->get_data(null, $l_object['isys_obj__id'])
                ->__to_array();

            // Expiration date:
            $l_expiration_date = $l_cat_data['isys_catg_certificate_list__expire'];

            // Not enough data provided. Skipping.
            if (!isset($l_expiration_date) || $l_expiration_date === '0000-00-00 00:00:00' || $l_expiration_date === '0000-00-00' || $l_expiration_date === '1970-01-01')
            {
                $this->m_log->info('Expiration date not set. Skip object.');
                continue;
            } //if

            $l_expiration_date = strtotime($l_expiration_date);

            $l_destined_date           = $l_expiration_date - $l_threshold;
            $l_formatted_destined_date = date('Y-m-d H:i:s', $l_destined_date);

            $this->m_log->info(
                sprintf(
                    'Threshold date is: %s',
                    $l_formatted_destined_date
                )
            );

            if ($l_destined_date > $l_now)
            {
                $this->m_log->info(
                    sprintf(
                        'Threshold date not exceeded (%s). Skip certificate.',
                        $l_formatted_destined_date
                    )
                );
                continue;
            } //if

            $this->m_log->info(
                'Threshold exceeded! Add certificate to the list.'
            );

            $l_licenses[] = $l_object;
        } //foreach

        unset($l_cmdb_dao);

        $l_num = count($l_licenses);

        if ($l_num == 0)
        {
            $this->m_log->info(
                'There are no certificates left to report. Skip notification.'
            );

            return $this->reset_counter($p_notification);
        }
        else
        {
            $this->m_log->info(
                sprintf(
                    'Amount of certificate which match the criterias: %s',
                    $l_num
                )
            );
        } //if

        // Write messages:
        if ($this->write_messages($p_notification, $l_licenses) > 0)
        {
            return $this->increase_counter($p_notification);
        } //if

        // Do not increase or reset counter...
    }

} //class

?>