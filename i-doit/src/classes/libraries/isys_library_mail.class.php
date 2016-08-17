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
 * phpMailer wrapper
 * Implements the phpMailer API for sending mails.
 * Is licensed under LGPL.
 *
 * @package    i-doit
 * @subpackage Libraries
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_library_mail extends PHPMailer
{
    /**
     * Content type for HTML emails
     */
    const C__CONTENT_TYPE__HTML = 'text/html';

    /**
     * Content type for plain text emails
     */
    const C__CONTENT_TYPE__PLAIN = 'text/plain';

    /**
     * Sendmail as mail backend
     */
    const C__BACKEND__SENDMAIL = 'sendmail';

    /**
     * SMTP server as mail backend
     */
    const C__BACKEND__SMTP = 'smtp';

    /**
     * PHP's mail() as mail backend
     */
    const C__BACKEND__MAIL = 'mail';

    /**
     * qmail as mail backend
     */
    const C__BACKEND__QMAIL = 'qmail';

    /**
     * Mail signature
     *
     * @var string
     */
    protected $m_signature;

    /**
     * @param $p_mail
     *
     * @return bool
     */
    public function check_address($p_mail)
    {
        $l_regex = $this->get_mail_regex();

        /* Check mail address */
        if (preg_match("/^$l_regex$/", $p_mail))
        {
            /* Check if domain is existent as DNS record */
            $l_mailParts  = explode("@", $p_mail);
            $l_mailDomain = array_pop($l_mailParts);

            $l_tmp = gethostbyname($l_mailDomain);
            if (!empty($l_tmp))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets subject.
     *
     * @return string
     */
    public function get_subject()
    {
        return $this->Subject;
    }

    /**
     * Sets subject.
     *
     * @param   string $p_subject
     *
     * @return  isys_library_mail
     */
    public function set_subject($p_subject)
    {
        assert('is_string($p_subject)');
        $this->Subject = $p_subject;

        return $this;
    }

    /**
     * Gets content type.
     *
     * @return string
     */
    public function get_content_type()
    {
        return $this->ContentType;
    } //function

    /**
     * Sets content type.
     *
     * @param   string $p_content_type
     *
     * @return  isys_library_mail
     * @throws  isys_exception_general for unknown content types
     */
    public function set_content_type($p_content_type)
    {
        assert('is_string($p_content_type)');
        switch ($p_content_type)
        {
            case self::C__CONTENT_TYPE__HTML:
            case self::C__CONTENT_TYPE__PLAIN:
                $this->ContentType = $p_content_type;
                break;
            default:
                throw new isys_exception_general('Unknown content type.');
                break;
        } //switch

        return $this;
    } //function

    /**
     * Gets body (email message).
     *
     * @return string
     */
    public function get_body()
    {
        return $this->Body;
    } //function

    /**
     * Sets body (email message).
     *
     * @param   string $p_body
     *
     * @return  isys_library_mail
     */
    public function set_body($p_body)
    {
        assert('is_string($p_body)');
        assert('isset($this->ContentType)');
        switch ($this->ContentType)
        {
            case 'text/html':
                $this->Body = nl2br($p_body);
                break;
            case 'text/plain':
                $this->Body = $p_body;
                break;
        } // switch

        return $this;
    } //function

    /**
     * Gets last error.
     *
     * @return string
     */
    public function get_last_error()
    {
        return $this->ErrorInfo;
    } // function

    /**
     * Sets backend.
     *
     * @param   string $p_backend See backend constants for supported values.
     *
     * @return  isys_library_mail
     * @throws  isys_exception_general for unknown backends
     */
    public function set_backend($p_backend)
    {
        assert('is_string($p_backend)');
        switch ($p_backend)
        {
            case self::C__BACKEND__MAIL:
                $this->IsMail();
                break;
            case self::C__BACKEND__QMAIL:
                $this->IsQmail();
                break;
            case self::C__BACKEND__SENDMAIL:
                $this->IsSendmail();
                break;
            case self::C__BACKEND__SMTP:
                $this->IsSMTP();
                break;
            default:
                throw new isys_exception_general('Unknown backend.');
                break;
        } // switch

        return $this;
    } // function

    /**
     * Adds default signature text.
     */
    public function add_default_signature()
    {
        global $g_config, $g_product_info;

        $this->m_signature .= PHP_EOL . '-- ' . PHP_EOL . 'i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['type'] . PHP_EOL . '<http' . (C__HTTPS_ENABLED ? 's' : '') . '://' . C__HTTP_HOST . $g_config['www_dir'] . '>' . PHP_EOL . 'i-doit -- CMDB and IT documentation <http://www.i-doit.com/>' . PHP_EOL;
    } //function

    /**
     * Gets signature text.
     *
     * @return string
     */
    public function get_signature()
    {
        return $this->m_signature;
    } // function

    /**
     * Sets signature text.
     *
     * @param   string $p_signature
     *
     * @return  isys_library_mail
     */
    public function set_signature($p_signature)
    {
        assert('is_string($p_signature)');
        $this->m_signature = $p_signature;

        return $this;
    } //function

    /**
     * Sets charset.
     *
     * @param  string $p_charset
     *
     * @return  isys_library_mail
     */
    public function set_charset($p_charset = 'iso-8859-1')
    {
        $this->CharSet = $p_charset;

        return $this;
    } //function

    /**
     * Sends current mail
     *
     * @return bool
     * @throws isys_exception_general
     */
    public function send()
    {
        $this->Body .= $this->m_signature;

        if ($this->Host)
        {
            if (parent::Send() === false)
            {
                throw new isys_exception_general($this->ErrorInfo);
            }

            return true;
        }

        return false;
    } // function

    /**
     * @return string
     */
    private function get_mail_regex()
    {
        $l_nonascii = "\x80-\xff";

        $l_nqtext = "[^\\\\$l_nonascii\015\012\"]";
        $l_qchar  = "\\\\[^$l_nonascii]";

        $l_protocol = '(?:mailto:)';

        $l_normuser     = '[a-zA-Z0-9][a-zA-Z0-9_.-]*';
        $l_quotedstring = "\"(?:$l_nqtext|$l_qchar)+\"";
        $l_user_part    = "(?:$l_normuser|$l_quotedstring)";

        $l_dom_mainpart = '[a-zA-Z0-9][a-zA-Z0-9._-]*\\.';
        $l_dom_subpart  = '(?:[a-zA-Z0-9][a-zA-Z0-9._-]*\\.)*';
        $l_dom_tldpart  = '[a-zA-Z]{2,5}';
        $l_domain_part  = "$l_dom_subpart$l_dom_mainpart$l_dom_tldpart";

        $l_regex = "$l_protocol?$l_user_part\@$l_domain_part";

        return $l_regex;
    } // function

    /**
     * isys_library_mail constructor.
     */
    public function __construct()
    {
        global $g_comp_session, $g_dirs;

        parent::__construct(false);

        if (isys_settings::get('system.email.smtp-host', ''))
        {
            $this->Host      = isys_settings::get('system.email.smtp-host', '');
            $this->Port      = isys_settings::get('system.email.port', '25');
            $this->Username  = isys_settings::get('system.email.username', '');
            $this->Password  = isys_settings::get('system.email.password', '');
            $this->From      = isys_settings::get('system.email.from', 'i-doit@i-doit.com');
            $this->FromName  = isys_settings::get('system.email.name', 'i-doit');
            $this->WordWrap  = 72;
            $this->Subject   = isys_settings::get('system.email.subject-prefix', '');
            $this->Timeout   = isys_settings::get('system.email.connection-timeout', '60');
            $this->SMTPDebug = isys_settings::get('system.email.smtpdebug', '0');

            if ($this->Username != '' && $this->Password != '')
            {
                $this->SMTPAuth = true;
            } // if

            if (is_object($g_comp_session))
            {
                $this->SetLanguage($g_comp_session->get_language(), $g_dirs["class"] . "/libraries/phpmailer/language/");
            }
        }
        else
        {
            throw new Exception("No mail server configured! You can configure it under \"Administration -> System settings.\"");
        }

    } //function
} //class