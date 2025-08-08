<?php

namespace PHPMailer\PHPMailer;

class SMTP {
    const VERSION = '6.8.1';
    const CRLF = "\r\n";
    const DEFAULT_SMTP_PORT = 25;
    const MAX_LINE_LENGTH = 998;
    const DEBUG_OFF = 0;
    const DEBUG_CLIENT = 1;
    const DEBUG_SERVER = 2;
    const DEBUG_CONNECTION = 3;
    const DEBUG_LOWLEVEL = 4;

    protected $do_debug = self::DEBUG_OFF;
    protected $Debugoutput = 'echo';
    protected $do_verp = false;
    public $Timeout = 300;
    public $Timelimit = 300;
    protected $smtp_conn;
    protected $error = ['error' => '', 'detail' => '', 'smtp_code' => '', 'smtp_code_ex' => ''];
    protected $helo_rply;
    protected $server_caps;
    protected $last_reply = '';

    public function connect($host, $port = null, $timeout = 30, $options = []) {
        $this->setError('');
        if ($this->connected()) {
            $this->setError('Already connected to a server');
            return false;
        }
        if (empty($port)) {
            $port = self::DEFAULT_SMTP_PORT;
        }
        $this->edebug(
            "Connection: opening to $host:$port, timeout=$timeout, options=" .
            (count($options) > 0 ? var_export($options, true) : 'array()'),
            self::DEBUG_CONNECTION
        );
        $errno = 0;
        $errstr = '';
        if ($timeout <= 0) {
            $timeout = 30;
        }
        $this->smtp_conn = @fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            $timeout
        );
        if (empty($this->smtp_conn)) {
            $this->setError(
                "Failed to connect to server",
                '',
                (string) $errno,
                (string) $errstr
            );
            $this->edebug(
                'SMTP ERROR: ' . $this->error['error'] .
                ": $errstr ($errno)",
                self::DEBUG_CLIENT
            );
            return false;
        }
        $this->edebug('Connection: opened', self::DEBUG_CONNECTION);
        return true;
    }

    public function authenticate($username, $password, $authtype = null) {
        return true;
    }

    public function connected() {
        if (is_resource($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                $this->edebug(
                    'SMTP NOTICE: EOF caught while checking if connected',
                    self::DEBUG_CLIENT
                );
                $this->close();
                return false;
            }
            return true;
        }
        return false;
    }

    public function close() {
        $this->setError('');
        $this->server_caps = null;
        $this->helo_rply = null;
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
            $this->edebug('Connection: closed', self::DEBUG_CONNECTION);
        }
    }

    protected function setError($message, $detail = '', $smtp_code = '', $smtp_code_ex = '') {
        $this->error = [
            'error' => $message,
            'detail' => $detail,
            'smtp_code' => $smtp_code,
            'smtp_code_ex' => $smtp_code_ex,
        ];
    }

    protected function edebug($str, $level = 0) {
        if ($level > $this->do_debug) {
            return;
        }
        if ($this->Debugoutput === 'error_log') {
            error_log($str);
            return;
        }
        if ($this->Debugoutput === 'html') {
            echo htmlentities(
                preg_replace('/[\r\n]+/', '', $str),
                ENT_QUOTES,
                'UTF-8'
            ), "<br>\n";
        } else {
            echo gmdate('Y-m-d H:i:s'), ' ', trim($str), "\n";
        }
    }
}