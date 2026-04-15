<?php

namespace Company\Async;

use Company\Exception\NotFoundException;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Exceptions\{FastCGIClientException};
use hollodotme\FastCGI\Requests\PostRequest;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;

class Async {

    protected $client;
    protected $connection;

    static $PHP_FPM_SOCKET_PATH = "/run/php-fpm/www.sock";

    public function __construct() {
        $this->client = new Client();
        $this->connection = new UnixDomainSocket(self::$PHP_FPM_SOCKET_PATH);
    }

    /**
     * @param string $workerFile absolute path to file
     * @param string|array $params post params
     * @param int $timeout timeout in milliseconds
     * @throws NotFoundException
     * @throws FastCGIClientException
     */
    function call(string $workerFile, $params, $timeout = 30000) {

        if (!is_file($workerFile))
            throw new NotFoundException("WorkerFile not found");

        $request = new PostRequest($workerFile, http_build_query($params));

        $socketID = -1;

        try {
            $socketID = $this->client->sendAsyncRequest($this->connection, $request);

        } catch (FastCGIClientException $e) {
//            file_put_contents("logger.txt", $e->getMessage() . "\n", FILE_APPEND);
//            var_dump($e->getMessage());die();
        }

        return $socketID;

    }

    /**
     * @return Client
     */
    public function getClient(): Client {
        return $this->client;
    }
}