<?php


namespace Company\Async;


use hollodotme\FastCGI\Client;

class Promise {

    protected $client;
    protected $socketID;
    protected $timeout;
    protected $error;

    public function __construct(Client $client, string $socketID, $timeout, $error = null) {
        $this->client = $client;
        $this->socketID = $socketID;
        $this->timeout = $timeout;
        $this->error = $error;
    }

    function getResponse(){
        try {
            $resp = $this->client->readResponse(
                $this->socketID,
                $this->timeout
            );

            $this->client->getSockets()->remove($this->socketID);
            return $resp;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }

        return null;
    }

    function getError() {
        return $this->error;
    }

    function cancel() {
        $this->client->getSockets()->remove($this->socketID);
    }

}