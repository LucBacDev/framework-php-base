<?php

namespace Company\Service\Lib;

use Company\Service\Model\ServiceMapper;

class Service
{
    protected $id;
    protected $name;
    protected $command;
    protected $attrs;

    /**
     * @param $id
     * @param $name
     * @param $command
     * @param $attrs
     */
    public function __construct($id, $name, $command, $attrs)
    {
        $this->id = $id;
        $this->name = $name;
        $this->command = $command;
        $this->attrs = $attrs;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getAttrs()
    {
        return $this->attrs;
    }

    /**
     * @param mixed $attrs
     */
    public function setAttrs($attrs): void
    {
        $this->attrs = $attrs;
    }

    public function attr($key, $defaultValue = null) {
        return arrData($this->attrs, $key, $defaultValue);
    }

    public function saveToDB() {
        try {
            ServiceMapper::makeInstance()->updateService(null, [
                "id" => $this->id,
                "name" => $this->name,
                "command" => $this->command,
                "attrs" => json_encode($this->attrs),
            ]);
        } catch (\Exception $e) {
            // existed
        }
    }

    public function start() {
        if($this->command === null) {
            return 0;
        }

        $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
        exec($command ,$op);
        return (int)$op[0];
    }

    public function stop() {
        $pid = $this->getPID();

        try {
            $result = shell_exec(sprintf('kill %d 2>&1', $pid));
            if (!preg_match('/No such process/', $result)) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    public function getPID() {
        $output = shell_exec("ps aux | grep '$this->command' | grep -v grep | awk {'print $2'}");
        preg_match_all('/\d+/', $output, $matches);

        $output = $matches[0];

        if (count($output) > 0)
            return $output[0];

        return 0;
    }
}