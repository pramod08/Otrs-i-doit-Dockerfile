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
 * iTop ticketing connector
 *
 * @package    i-doit
 * @subpackage Connector
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_connector_ticketing_itop extends isys_connector_ticketing
{

    /**
     * HTTP Protocol
     *
     * @var isys_protocol_http
     */
    protected $m_protocol = null;
    /**
     * Response map
     * This mapping maps iTop response values to an internally defined i-doit format.
     *
     * @var array
     */
    protected $m_response_map = [
        'id'                      => parent::C__FIELD__ID,
        'Queue'                   => parent::C__FIELD__QUEUE,
        'caller_id_friendlyname'  => parent::C__FIELD__OWNER,
        'caller_id_friendlyname2' => parent::C__FIELD__CREATOR,
        'status'                  => parent::C__FIELD__STATUS,
        'title'                   => parent::C__FIELD__SUBJECT,
        'priority'                => parent::C__FIELD__PRIORITY,
        'priority2'               => parent::C__FIELD__INITIALPRIORITY,
        'priority3'               => parent::C__FIELD__FINALPRIORITY,
        'Requestors'              => parent::C__FIELD__REQUESTORS,
        'Cc'                      => parent::C__FIELD__CC,
        'AdminCc'                 => parent::C__FIELD__ADMINCC,
        'start_date'              => parent::C__FIELD__CREATED,
        'start_date2'             => parent::C__FIELD__STARTS,
        'start_date3'             => parent::C__FIELD__STARTED,
        'Due'                     => parent::C__FIELD__DUE,
        'Resolved'                => parent::C__FIELD__RESOLVED,
        'Told'                    => parent::C__FIELD__TOLD,
        'last_update'             => parent::C__FIELD__LASTUPDATED,
        'TimeEstimated'           => parent::C__FIELD__TIMEESTIMATED,
        'TimeWorked'              => parent::C__FIELD__TIMEWORKED,
        'TimeLeft'                => parent::C__FIELD__TIMELEFT
    ];
    /**
     * @var string
     */
    private $m_rt_base_url = null;
    /**
     * @var string
     */
    private $m_ticket_url_pattern = '/pages/UI.php?operation=details&class=UserRequest&id=%s&c[org_id]=1&c[menu]=UserRequest';
    /**
     * Base URL of the Rest interface
     *
     * @var string
     */
    private $m_url_base = '/webservices/rest.php?version=1.0';

    /**
     * Set the ticketsystem base url
     *
     * @param int $p_object_id Object identifier
     *
     * @return string
     *
     * @todo change method name
     */
    public function create_new_ticket_url($p_object_id)
    {
        return [
            'url' => $this->m_rt_base_url . '/pages/UI.php?operation=new&class=UserRequest&c%5Borg_id%5D=1&c%5Bmenu%5D=NewUserRequest'
        ];
    }

    public function get_attachment_content($p_attachment_id)
    {

    }

    public function get_attachments($p_ticket_id)
    {

    }

    public function get_comments($p_ticket_id)
    {

    }

    /**
     * Get ticket history by ticket id
     *
     * @param string|int $p_ticket_id
     * @param string     $p_history_type
     *
     * @return array
     */
    public function get_history($p_ticket_id, $p_history_type = null)
    {
        if (!empty($p_ticket_id))
        {
            return $this->generic_request('ticket/' . $p_ticket_id . '/history?format=l');
        }

        return false;
    }

    public function get_queue($p_queue_id = null)
    {

    } //function

    /**
     * Returns tickets (or only one ticket by id)
     *
     * @param int $p_ticket_id
     *
     * @return string
     */
    public function get_ticket($p_ticket_id)
    {
        /**
         * TODO: Needed for workstations
         */
    }

    /**
     * @param $p_ticketID
     *
     * @return string
     */
    public function get_ticket_url($p_ticketID)
    {
        return $this->m_protocol->get_host() . sprintf($this->m_ticket_url_pattern, $p_ticketID);
    }

    public function get_tickets($p_tickets)
    {
        // Not used in code
    }

    /**
     * Fetches tickets by CMDB object identifier
     *
     * @param int $p_object_id Object identifier
     *
     * @return array
     */
    public function get_tickets_by_cmdb_object($p_object_id)
    {
        // Build parameter array
        $l_params = [
            'operation' => 'core/get',
            'class'     => 'UserRequest',
            'key'       => 'SELECT UserRequest AS u JOIN lnkFunctionalCIToTicket AS l ON l.ticket_id = u.id JOIN FunctionalCI AS f ON l.functionalci_id = f.id WHERE f.idoit_id = ' . $p_object_id
        ];

        // Perform a generic request and get the desired tickets for the ci
        return $this->generic_request($l_params);
    }

    /**
     * Get ticket(s) by status
     *
     * @param mixed $p_status
     *
     * @return string
     */
    public function get_tickets_by_status($p_status)
    {
        // Not used in code
    }

    public function get_users($p_user_id = null)
    {

    }

    public function login($p_user, $p_pass)
    {

    }

    public function logout()
    {

    }

    public function search($p_params)
    {

    }

    /**
     * Fetches tickets by a list of custom fields.
     *
     * @param array  $p_custom_fields Custom fields. Associative array with cf
     *                                names as keys and the destinated values as values.
     * @param string $p_order_by      (optional)
     *                                By this parameter you can change the sort field and order of the search result. To sort a list
     *                                ascending just put a + before the fieldname, otherwise a -. Eg: -Created (will put the newest
     *                                tickets at the beginning). Defaults to null (no ordering).
     *
     * @return array
     */
    public function get_tickets_by_custom_field($p_custom_fields, $p_order_by = null)
    {
        // Not needed
    }

    /**
     * Get ticket links
     *
     * @param int $p_ticket_id
     */
    public function get_ticket_links($p_ticket_id)
    {
        // Not used in code
    }

    /**
     * Get request (e.g. get available tickets)
     *
     * @param array $p_params
     *
     * @return string
     */
    private function request($p_params = [])
    {
        return $this->m_protocol->post(
            null,
            [
                'version'   => '1.0',
                'auth_user' => $this->m_user,
                'auth_pwd'  => $this->m_pass,
                'json_data' => isys_format_json::encode($p_params)
            ]
        );
    }

    /**
     * Formats an RT result
     *
     * @param string $p_raw_data
     *
     * @return array
     * @throws isys_exception_general
     */
    private function format_response($p_raw_data)
    {
        $l_result = [];

        // Check for a valid json response first
        if (isys_format_json::is_json($p_raw_data))
        {
            // Encode charset and notation
            $p_raw_data = isys_format_json::decode($p_raw_data);

            // Check for returned code
            if (is_array($p_raw_data) && isset($p_raw_data['code']) && $p_raw_data['code'] == 0)
            {
                // Check for filled resultset
                if (isset($p_raw_data['objects']) && is_array($p_raw_data['objects']))
                {
                    // Map the response
                    foreach ($p_raw_data['objects'] AS $l_ticket)
                    {
                        // Some defaulting stuff and manipulations
                        $l_ticket['fields']['id']                      = $l_ticket['key'];
                        $l_ticket['fields']['caller_id_friendlyname2'] = $l_ticket['fields']['caller_id_friendlyname'];
                        $l_ticket['fields']['priority2']               = $l_ticket['fields']['priority3'] = $l_ticket['fields']['priority'];
                        $l_ticket['fields']['start_date2']             = $l_ticket['fields']['start_date3'] = $l_ticket['fields']['start_date'];

                        // Build array
                        $l_result[$l_ticket['key']] = $this->map_response($l_ticket['fields']);
                    } // foreach
                } // if
            }
            else
            {
                throw new isys_exception_general('An error occured while querying the api: ' . $p_raw_data['message']);
            } // if
        }
        else
        {
            throw new isys_exception_general('iTop API response is not valid.');
        } // if

        return $l_result;
    }

    /**
     * Prepare a generic post request
     *
     * @param $p_params
     *
     * @return string
     * @throws isys_exception_general
     * @internal param string $p_url
     */
    private function generic_request($p_params)
    {
        $l_raw_data = $this->request($p_params);

        if ($l_raw_data)
        {
            return $this->format_response($l_raw_data);
        }

        return [];
    }

    /**
     * Injects the used protocol and sets RT's base url.
     *
     * @param isys_protocol $p_protocol
     */
    public function __construct($p_protocol)
    {
        parent::__construct($p_protocol);

        $this->m_ticket_url_pattern = $this->m_protocol->get_base_url() . $this->m_ticket_url_pattern;
        $this->m_rt_base_url        = $this->m_protocol->get_host() . $this->m_protocol->get_base_url();
        $this->m_protocol->attach_base_url($this->m_url_base);
    }
} //class
