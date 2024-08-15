<?php

class App
{
    private $uri = null;
    private $method = null;

    function __construct()
    {
        $route = (object)Router::getData();
        $this->uri = $route->uri;
        $this->method = $route->method;
    }

    public function get($uri, $execution, $authorization = false)
    {
        if (strtolower($this->method) !== 'get') {
            return false;
        }

        preg_match_all('/(\{(.*)\})/isuU', $uri, $match, PREG_PATTERN_ORDER);

        if (isset($match[2]) && !empty($match[2])) {
            $key_arr = $match[2];
            $preg = preg_replace('/([\/\-])/', '\\\\$1', $uri);
            $preg = '/^' . preg_replace('/(\{.*\})/U', '([^\/$]*)', $preg) . '\/?$/ismuU';
            preg_match_all($preg, urldecode($this->uri), $match2, 2);

            if (!$match2 || ((count($match2[0]) - 1) != count($key_arr))) {
                return false;
            }

            $args = [];
            foreach ($match2[0] as $index => $arg_arr) {
                if (!!$index) {
                    $key = trim($key_arr[$index - 1]);
                    $args[$key] = trim($arg_arr);
                }
            }

            if ($authorization) {
                CurrentUser::is_authorized();
            }

            $execution($args);
        } else {
            if ($this->uri === $uri) {
                if ($authorization) {
                    CurrentUser::is_authorized();
                }
                $execution();
            } else {
                return false;
            }
        }
    }

    public function delete($uri, $execution, $authorization = false)
    {
        if (strtolower($this->method) !== 'delete') {
            return false;
        } elseif ($this->uri === $uri) {
            if ($authorization) {
                CurrentUser::is_authorized();
            }
            $execution();
        } else {
            preg_match_all('/(\{(.*)\})/U', $uri, $match, PREG_PATTERN_ORDER);
            if (isset($match[2]) && !empty($match[2])) {
                if ($authorization) {
                    CurrentUser::is_authorized();
                }

                $key_arr = $match[2];
                $preg = preg_replace('/([\/\-])/', '\\\\$1', $uri);
                $preg = '/^' . preg_replace('/(\{.*\})/U', '(.*)', $preg) . '$/U';
                preg_match_all($preg, $this->uri, $match2, 2);

                if (!count($match2) || (count($match2) != count($key_arr))) {
                    return false;
                }

                $args = [];
                foreach ($match2 as $index => $arg_arr) {
                    $key = $key_arr[$index];
                    $args[$key] = $arg_arr[1];
                }
                $execution($args);
            }

            return false;
        }
    }

    public function post($uri, $execution, $authorization = false)
    {
        if (strtolower($this->method) !== 'post') {
            return false;
        } elseif ($this->uri === $uri) {
            if ($authorization) {
                CurrentUser::is_authorized();
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if ($input) {
                try {
                    $execution($input);
                } catch (Exception $e) {
                    self::json([
                        'reason' => 'Bad Request',
                        'message' => 'Error in saving stats: ' . $e->getMessage(),
                    ], 400);
                }
            } else {
                self::json([
                    'reason' => 'Bad Request',
                    'message' => 'No data provided',
                ], 400);
            }
        } else {
            return false;
        }
    }

    public static function json($content = [], $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($content);
        exit;
    }
}
