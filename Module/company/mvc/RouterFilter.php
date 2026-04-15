<?php

namespace Company\MVC;

class RouterFilter
{

    protected mixed $filterUri;
    protected mixed $filterIsRest;
    public function __construct($filterUri = '', $filterIsRest = '')
    {
        $this->filterUri = $filterUri;
        $this->filterIsRest = $filterIsRest;
    }
}