<?php
namespace Dropbox;

/**
 * A minimal wrapper around a cURL handle.
 *
 * @internal
 */
final class Curl
{
    /** @var resource */
    public $handle;

    /** @var string[] */
    private $headers = array();

    /**
     * @param string $url
     */
    function __construct($url)
    {
        $this->handle = curl_init($url);

        // Force SSL and use our own certificate list.
        $this->set(CURLOPT_SSL_VERIFYPEER, true);
        $this->set(CURLOPT_SSL_VERIFYHOST, 2);
        $this->set(CURLOPT_SSLVERSION, 3);  // Force SSL v3.
        $this->set(CURLOPT_CAINFO, __DIR__."/trusted-certs.crt");

        // Limit vulnerability surface area.
        $this->set(CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        $this->set(CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
    }

    /**
     * @param string $header
     */
    function addHeader($header)
    {
        $this->headers[] = $header;
    }

    function exec()
    {
        $this->set(CURLOPT_HTTPHEADER, $this->headers);

        $body = curl_exec($this->handle);
        if ($body === false) {
            throw new Exception_NetworkIO("Error executing HTTP request: " . curl_error($this->handle));
        }

        $statusCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        return new HttpResponse($statusCode, $body);
    }

    /**
     * @param int $option
     * @param mixed $value
     */
    function set($option, $value)
    {
        curl_setopt($this->handle, $option, $value);
    }

    function __destruct()
    {
        curl_close($this->handle);
    }
}
