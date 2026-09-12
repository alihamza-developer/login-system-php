<?php

namespace Core;


class App
{
    private static $made = [];

    # Build once, hand back the same one after that
    public static function get($class)
    {
        if (!isset(self::$made[$class])) self::$made[$class] = new $class();
        return self::$made[$class];
    }

    # Drop everything, used by long running scripts
    public static function reset()
    {
        self::$made = [];
    }

    # Core
    public static function db()          { return self::get(Database::class); }
    public static function functions()   { return self::get(Functions::class); }
    public static function ext()         { return self::get(Extension::class); }
    public static function delete()      { return self::get(Delete::class); }

    # Auth
    public static function auth()        { return self::get(\Auth\Auth::class); }
    public static function session()     { return self::get(\Auth\Session::class); }
    public static function token()       { return self::get(\Auth\Token::class); }
    public static function guard()       { return self::get(\Auth\Guard::class); }
    public static function guest()       { return self::get(\Auth\Guest::class); }
    public static function settings()       { return self::get(\Auth\Settings::class); }

    # Outside services
    public static function email()  { return self::get(\Service\Emails::class); }
}
