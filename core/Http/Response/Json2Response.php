<?php


namespace core\Http\Response;

use GuzzleHttp\Psr7\Response as guzzleResponse;

class Json2Response implements ResponseInterface
{
    private $response;

    public function __construct($body = null, $statusCode = 200, $headers = [], $statusReason = null, $version = '1.1')
    {
        $headers['Content-type'] = 'application/json';
        if (is_resource($body)) {
            $body = sprintf('a resource of %s', get_resource_type($body));
        }
        $body = json_encode($body);
        $this->response = new guzzleResponse($statusCode, $headers, $body, $version, $statusReason);
    }

    private function sendHeaders()
    {
        foreach ($this->response->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                header($header . ': ' . $value);
            }
        }

        header(sprintf('HTTP/%s %s %s', $this->response->getProtocolVersion(), $this->response->getStatusCode(), $this->response->getReasonPhrase()));
    }

    private function sendBody()
    {
        $body = $this->response->getBody()->getContents();
        echo $body;
    }

    public function render()
    {
        // TODO: Implement render() method.
        $this->sendHeaders();
        $this->sendBody();
    }

}