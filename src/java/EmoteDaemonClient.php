<?php declare(strict_types=1);

namespace java;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Class to communicate with the Emote formatter daemon (a Java service using socket TCP)
 */
class EmoteDaemonClient
{
    private \Socket $sock;

    private array $dataToSend = array();

    public function __construct()
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        $address = gethostbyname('java');

        socket_connect($this->sock,$address, 3000);

    }

    public function addData(string $data, int $type): void {
        $this->dataToSend[] = array('data' => $data, 'type' => $type);
    }

    /**
     * @param array $requestData
     * @return array|null null if failed to get result
     */
    public function exchange(array $requestData): ?array
    {
        $data = pack('c', sizeof($this->dataToSend));
        foreach ($this->dataToSend as $item) {
            $data .= pack('c', $item['type']);
            $data .= pack('N', strlen($item['data']));
            $data .= $item['data'];
        }
        $data .= pack('c', sizeof($requestData));
        foreach ($requestData as $item) {
            $data .= pack('c', $item);
        }
        $this->writeSocket($data);

        $payload = $this->readSocket();
        if ($payload === null) return null;

        $len = unpack('c', $payload)[1];
        $pos = 1;
        $result = array();
        for ($i = 0; $i < $len; $i++) {
            $type = unpack('c', $payload, $pos++)[1];
            $size = unpack('N', $payload, $pos)[1];
            $pos += 4;

            $data = substr($payload, $pos, $size);
            $pos += $size;
            $result[] = array('type' => $type, 'data' => $data);
        }
        return $result;
    }


    private function writeSocket(string $binary): void
    {
        $size = strlen($binary);
        $data = pack('N', $size).$binary; //very useful function
        socket_write($this->sock, $data, strlen($data));
    }

    /**
     * @link https://stackoverflow.com/questions/27631009/php-get-packet-length
     * but modified for my binary purposes
     * @return string|null data
     */
    private function readSocket(): ?string {
        socket_recv($this->sock, $r, 4, flags: MSG_WAITALL);
        if ($r === null) return null;
        $la = unpack("N", $r)[1];

        $len = $la;
        $time = 0;
        $payload = "";
        while ($len > 0 && $time < 10) {
            //$data = socket_read($this->sock, $la, mode: PHP_BINARY_READ);
            $tlen = socket_recv($this->sock, $data, $la, flags: MSG_WAITALL);
            //$tlen = strlen($data);
            $payload .= $data;
            $len -= $tlen;
            if ($len == 0) {
                break;
            }
            usleep(100);
            $time++;
        }
        return $payload;
    }

    public function __destruct()
    {
        socket_close($this->sock);
    }
}