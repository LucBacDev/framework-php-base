<?php

namespace Company\MVC;

interface UiLoadable {

    /**
     * Load các thư viện css, js, jsx vào layout
     * @param \Company\MVC\Layout $layout
     */
    function load(Layout $layout);
}
