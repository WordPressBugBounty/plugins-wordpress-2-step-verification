<?php

class Wp2sv_Ntp_Socket
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * Build a new instance of a socket
     *
     * @param string $host    The ntp server url
     * @param int    $port    The port the ntp server is listening on
     * @param int    $timeout The timeout duration of the connection
     * @throws Exception
     */
    public function __construct($host, $port = 123, $timeout = 5)
    {
        $this->host = $this->resolveAddress($host);
        $this->port = $port;
        $this->timeout = $timeout;

        $this->connect();
    }

    /**
     * Write data to the socket
     *
     * @param string $data The data to write
     *
     * @return void
     */
    public function write($data)
    {
        fwrite($this->resource, $data);
    }

    /**
     * Read data from the socket
     *
     * @throws Exception When the connection timed out
     * @return string
     */
    public function read()
    {
        $info = $this->getMetadata();

        if (true === $info['timed_out']) {
            throw new \Exception('Connection timed out');
        }

        return fread($this->resource, 48);
    }

    /**
     * Closes the socket connection
     *
     * @return void
     */
    public function close()
    {
        fclose($this->resource);
        $this->resource = null;
    }

    /**
     * Check if the connection is open
     *
     * @return bool
     */
    public function isConnected()
    {
        return is_resource($this->resource) && !feof($this->resource);
    }

    /**
     * Gets the full address from the socket
     *
     * @return string|null The address if there is a socket
     */
    public function getAddress()
    {
        if (false !== $this->resource) {
            return stream_socket_get_name($this->resource, false);
        }

        return null;
    }

    /**
     * Gets the ip address from the domain name
     *
     * @param string $host The domain name to resolve
     *
     * @return string
     */
    protected function resolveAddress($host)
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        $ip = gethostbyname($host);
        return "udp://{$ip}";
    }

    /**
     * Returns a stream's meta data
     *
     * @return array
     */
    protected function getMetadata()
    {
        return stream_get_meta_data($this->resource);
    }

    private function connect()
    {
        if (!$this->isConnected()) {
            $this->resource = @fsockopen(
                $this->host,
                $this->port,
                $errno,
                $errstr,
                $this->timeout
            );

            if (!$this->resource) {
                throw new \Exception("Unable to create socket: '{$errno}' '{$errstr}'");
            }
        }
    }
}
