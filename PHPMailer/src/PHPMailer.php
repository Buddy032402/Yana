<?php

namespace PHPMailer\PHPMailer;

class PHPMailer {
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';

    // Add encryption constants
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    public $Priority;
    public $CharSet = self::CHARSET_ISO88591;
    public $ContentType = self::CONTENT_TYPE_PLAINTEXT;
    public $Encoding = self::ENCODING_8BIT;
    public $ErrorInfo = '';
    public $From = '';
    public $FromName = '';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $Mailer = 'mail';
    public $WordWrap = 0;
    public $Hostname = '';
    public $MessageID = '';
    public $MessageDate = '';
    public $Host = 'localhost';
    public $Port = 25;
    public $Username = '';
    public $Password = '';
    public $SMTPAuth = false;
    public $SMTPSecure = '';
    public $Timeout = 300;
    public $SMTPDebug = 0;
    public $SMTPKeepAlive = false;

    public function __construct($exceptions = null) {
        if (null !== $exceptions) {
            $this->exceptions = (bool) $exceptions;
        }
    }

    public function isHTML($isHtml = true) {
        if ($isHtml) {
            $this->ContentType = static::CONTENT_TYPE_TEXT_HTML;
        } else {
            $this->ContentType = static::CONTENT_TYPE_PLAINTEXT;
        }
    }

    public function isSMTP() {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '') {
        $this->From = $address;
        $this->FromName = $name;
    }

    public function addAddress($address, $name = '') {
        $this->to[] = [$address, $name];
    }

    public function send() {
        try {
            if ($this->Mailer == 'smtp') {
                return $this->smtpSend();
            }
            return true;
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }

    protected function smtpSend() {
        if (!$this->SMTPConnect()) {
            throw new Exception('SMTP connection failed');
        }
        return true;
    }

    protected function SMTPConnect() {
        return true;
    }
}