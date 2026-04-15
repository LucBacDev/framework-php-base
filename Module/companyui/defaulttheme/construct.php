<?php

namespace CompanyUI\DefaultTheme;

use Company\MVC\Layout;
use Company\MVC\Theme;

if (app()->isRest() == false) {
    Theme::registerTheme(new DefaultTheme());
    Layout::registerLayout(new AdminLayout());
    Layout::registerLayout(new RisLayout());
}

