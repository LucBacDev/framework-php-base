<?php

namespace Company\MVC;

use Company\Exception as Ex;

class ExceptionHandler extends \Slim\Middleware\PrettyExceptions {

    function renderBody(&$env, $exception) {
        if ($exception instanceof Ex\BadRequestException) {
            app()->slim->response->setStatus(400);
        } else if ($exception instanceof Ex\ForbiddenException) {
            app()->slim->response->setStatus(403);
        } else if ($exception instanceof Ex\UnauthorizedException) {
            app()->slim->response->setStatus(401);
        } else if ($exception instanceof Ex\NotFoundException) {
            app()->slim->response->setStatus(404);
        } else {
            app()->slim->response->setStatus(500);
        }
        return parent::renderBody($env, $exception);
    }

}
