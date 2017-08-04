<?php
namespace core;

use core\view;
use app;

class http
{

    private static $handler;

    static function errorHandler($handler)
    {
        self::$handler = $handler;
    }

    static function json(array $data)
    {
        header("Content-Type:application/json;charset:utf-8");
        exit(json_encode($data, true));
    }

    static function error($code, $message = "")
    {
        switch ($code) {
            case 404:
                header("http/1.1 404 not found");
                exit(view::display('not_found', compact('message')));
                break;
        }
        if (empty(self::$handler)) {
            // echo $message;
            return true;
        }
        $handler = self::$handler;
        $handler($code, $message);
    }

    static function isPost()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            return true;
        }
        return false;
    }

    static function isGet()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            return true;
        }
        return false;
    }

    static function redirect($action = "")
    {
        header("location:" . $action);
    }

    static function setCookie($key, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    static function getCookie($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    static function removeCookie($key)
    {
        unset($_COOKIE[$key]);
    }

    static function destroyCookie()
    {
        unset($_COOKIE);
    }

    static function setSession($key, $value = null)
    {
        $_SESSION[$key] = $value;
    }

    static function sessionStart()
    {
        if (session_status() != 2) {
            session_start();
        }
    }

    static function removeSession($key)
    {
        unset($_SESSION[$key]);
    }

    static function getSession($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    static function destroySession()
    {
        session_destroy();
    }
}