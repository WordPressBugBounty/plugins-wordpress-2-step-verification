<?php
class Wp2sv_Ntp_Client
{
    /**
     * @var Wp2sv_Ntp_Socket
     */
    protected $socket;

    /**
     * Build a new instance of the ntp client
     *
     * @param Wp2sv_Ntp_Socket $socket The socket used for the connection
     */
    public function __construct(Wp2sv_Ntp_Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * Sends a request to the remote ntp server for the current time.
     * The current time returned is UTC.
     *
     * @return int
     * @throws Exception
     */
    public function getTime()
    {

        $packet = $this->buildPacket();
        $this->write($packet);

        $time = $this->unpack($this->read());
        $time -= 2208988800;

        $this->socket->close();

        return $time;
    }

    /**
     * Write a request packet to the remote ntp server
     *
     * @param string $packet The packet to send
     *
     * @return void
     */
    protected function write($packet)
    {
        $this->socket->write($packet);
    }

    /**
     * Reads data returned from the ntp server
     *
     * @return string
     * @throws Exception
     */
    protected function read()
    {
        return $this->socket->read();
    }

    /**
     * Builds the request packet for the current time
     *
     * @return string
     */
    protected function buildPacket()
    {
        $packet = chr(0x1B);
        $packet .= str_repeat(chr(0x00), 47);

        return $packet;
    }

    /**
     * Unpacks the binary data that was returned
     * from the remote ntp server
     *
     * @param string $binary The binary from the response
     *
     * @return string
     */
    protected function unpack($binary)
    {
        $data = unpack('N12', $binary);
        return sprintf('%u', $data[9]);

    }
}

