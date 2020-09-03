<?php


namespace core\Http\Response;


use core\Http\Exceptions\StatusCodeInvalidException;

class JsonResponse implements ResponseInterface
{
    private $version;

    private $headers;

    private $body;

    private $statusCode;

    private $statusReason;

    /** @var array Map of standard HTTP status code/reason phrases */
    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
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
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    private $cookies;

    public function __construct($body = null, $statusCode = 200, $headers = [], $statusReason = null, $cookies = [], $version = '1.1')
    {
        $this->setBody($body);
        $this->setStatus($statusCode, $statusReason);
        $this->setHeaders($headers);
        $this->setCookies($cookies);
        $this->version = $version;
        return $this;
    }

    private function setBody($body)
    {
        $this->body = $body;
    }

    private function setStatus($code, $reason)
    {
        $this->assertStatusCodeIsInteger($code);
        $this->assertStatusCodeRange($code);
        $this->statusCode = $this->body === null ? 204 : $code;
        $this->statusReason = $reason ?? self::$phrases[$this->statusCode];
    }

    private function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    private function setCookies($cookies)
    {
        $this->cookies = $cookies;
    }

    private function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        if(!empty($this->headers)) {
            foreach ($this->headers as $header => $values) {
                if (is_array($values)) {
                    foreach ($values as $value) {
                        header($header . ': ' . $value, false);
                    }
                } else {
                    header($header . ': ' . $values);
                }
            }
        }

        header('Content-type: application/json');

        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusReason));

    }

    private function sendBody()
    {
        if (is_string($this->body)) {
            echo $this->body;
        } else {
            if(is_resource($this->body)) {
                $this->body = sprintf('a resource of %s', get_resource_type($this->body));
            }
            echo json_encode($this->body);
        }
    }

    private function sendCookies()
    {
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $cookie) {
                setcookie(
                    $cookie['name'],
                    $cookie['value'],
                    $cookie['expire'] ?? 0,
                    $cookie['path'] ?? '',
                    $cookie['domain'] ?? '',
                    $cookie['secure'] ?? false,
                    $cookie['httponly'] ?? false
                );
            }
        }
    }

    private function assertStatusCodeIsInteger($statusCode)
    {
        if (filter_var($statusCode, FILTER_VALIDATE_INT) === false) {
            throw new StatusCodeInvalidException('Status code must be an integer value.');
        }
    }

    private function assertStatusCodeRange($statusCode)
    {
        if ($statusCode < 100 || $statusCode >= 600) {
            throw new StatusCodeInvalidException('Status code must be an integer value between 1xx and 5xx.');
        }
    }

    public function render()
    {
        // TODO: Implement render() method.
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }
}