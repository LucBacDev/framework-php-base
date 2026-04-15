<?php

namespace Company\MVC;

use Company\Exception as Ex;

/**
 * Customize slim framework
 */
class Slim extends \Slim\Slim {

    public function run() {
        set_error_handler(array('\Slim\Slim', 'handleErrors'));

        //Apply final outer middleware layers
        if ($this->config('debug')) {
            //Apply pretty exceptions only in debug to avoid accidental information leakage in production
            $this->add(new ExceptionHandler());
        }

        //Invoke middleware and application stack
        $this->middleware[0]->call();

        //Fetch status, header, and body
        list($status, $headers, $body) = $this->response->finalize();

        // Serialize cookies (with optional encryption)
        \Slim\Http\Util::serializeCookies($headers, $this->response->cookies, $this->settings);

        //Send headers
        if (headers_sent() === false) {
            //Send status
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header(sprintf('Status: %s', \Slim\Http\Response::getMessageForCode($status)));
            } else {
                header(sprintf('HTTP/%s %s', $this->config('http.version'), \Slim\Http\Response::getMessageForCode($status)));
            }

            //Send headers
            foreach ($headers as $name => $value) {
                if ($name == "Content-Type") {
                    foreach(headers_list() as $index => $string) {
                        if (strpos($string, "Content-Type") !== FALSE)
                            continue 2;
                    }
                }
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    header("$name: $hVal", false);
                }
            }
        }

        //Send body, but only if it isn't a HEAD request
        if (!$this->request->isHead()) {
            echo $body;
        }

        $this->applyHook('slim.after');

        restore_error_handler();
    }

    /**
     * Default Not Found handler
     */
    protected function defaultNotFound() {
        $this->error(new Ex\NotFoundException());
    }

    /**
     * Default Error handler
     */
    protected function defaultError($e) {
        $this->getLog()->error($e);
        $this->error(new \Exception());
    }

    public function error($argument = null) {
        if (is_callable($argument)) {
            //Register error handler
            $this->error = $argument;
        } else {
            //Invoke error handler
            if ($argument instanceof Ex\BadRequestException) {
                $this->response->setStatus(400);
            } else if ($argument instanceof Ex\ForbiddenException) {
                $this->response->setStatus(403);
            } else if ($argument instanceof Ex\UnauthorizedException) {
                $this->response->setStatus(401);
            } else if ($argument instanceof Ex\NotFoundException) {
                $this->response->setStatus(404);
            } else {
                $this->response->setStatus(500);
            }

            $headers = getallheaders();
            $contentType = strtolower(arrData($headers, 'content-type', 'text/html'));
            if ($contentType == 'application/json') {
                $this->response->header('Content-type', 'application/json');
            } else {
                $this->response->header('Content-type', 'text/html');
            }

            $this->response->body('');
            ob_start();
            require __DIR__ . '/error.php';
            $this->response->write(ob_get_clean());
            $this->stop();
        }
    }

}
