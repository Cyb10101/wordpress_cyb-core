<?php
namespace App\Utility;

class HttpUtility extends Singleton {
    /**
     * List of http response text
     *
     * @return array
     */
    protected static function getHttpResponseList(): array {
        $return = [
            100 => 'Continue',
            101 => 'Switching Protocols',

            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',

            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',

            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
        ];
        return $return;
    }

    /**
     * Get http response text by http code
     *
     * @param int $code
     * @return mixed
     */
    public static function getHttpResponseText(int $code) {
        $codes = self::getHttpResponseList();
        if (isset($codes[$code])) {
            return $codes[$code];
        }
        exit('Unknown http status code "' . htmlentities($code) . '"');
    }

    /**
     * Set http response
     *
     * @param int $code
     * @return void
     */
    public static function setHttpResponseCode(int $code) {
        $text = self::getHttpResponseText($code);
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . $code . ' ' . $text);
    }

    /**
     * Set http response and render information
     *
     * @param $code
     * @return void
     */
    public static function renderHttpStatusCode($code) {
        $text = self::getHttpResponseText($code);

        $content = '';
        if (!headers_sent()) {
            $content = '<title>Status ' . $code . ': ' . $text . '</title>';
        }
        $content .= '<h2>Status ' . $code . ': ' . $text . '</h2>'
            . '<a href="/">Back to domain root</a>';

        if (!headers_sent()) {
            header('Content-Type: text/html');
            self::setHttpResponseCode($code);
        }
        echo $content;
        if (!headers_sent()) {
            exit(str_repeat(' ', min(600, 600 - strlen($content))));
        }
    }

    /**
     * @param string $url
     * @param int $time
     * @return void
     */
    public static function redirect(string $url, int $time = 0) {
        // Strip out any line breaks
        $url = preg_split("/[\r\n]/", $url);
        $url = $url[0];

        if (headers_sent()) {
            if ($time > 0) {
                echo '<script>setTimeout("document.location.href=\'' . $url . '\';", ' . $time . '000);</script>\n';
            } else {
                echo '<script>document.location.href="' . $url . '";</script>\n';
            }
        } else {
            @ob_end_clean(); // clear output buffer
            if ($time > 0) {
                header('refresh:' . $time . '; url=' . $url);
            } else {
                self::setHttpResponseCode(301);
                header('Location: ' . $url);
            }
        }
        exit();
    }
}
