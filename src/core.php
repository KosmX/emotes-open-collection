<?php declare(strict_types=1);

/** This is CORE.php by KosmX
 *  Some very handy php function and DB manager.
 *  The dbSupplier will connect to a database, and allow anyone from the PHP to use the connection
 *  And with its destructor, it will auto-disconnect!
 */

class dbSupplier
{
    private function connectToDB(): mysqli
    {
        return new mysqli($_ENV['db_host'], $_ENV['db_user'], $_ENV['db_password'], $_ENV['db_db']);
    }


    private mysqli $db;
    private static dbSupplier|null $instance = null;

    private function __construct()
    {
        if (dbSupplier::$instance === null) {
            $this->db = $this->connectToDB();
            if ($this->db->connect_error) {
                echo $this->db->connect_error;
                die(503);
            }
            dbSupplier::$instance = $this;
        } else {
            die(500);
        }
    }

    function __destruct()
    {
        $this->db->close();
    }

    public static function getDB(): mysqli
    {
        if (dbSupplier::$instance === null) {
            new dbSupplier();
        }
        return dbSupplier::$instance->db;
    }
}

function getDB(): mysqli
{
    return dbSupplier::getDB();
}

/**
 * When not doing get or post, it can still extract the params from the URI
 * @return array http params
 */
function getURLParams(): array
{
    parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $params);
    return $params;
}

function getCurrentPage(): string
{
    return rtrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/');
}

function getUrlArray(): array
{
    return explode("/", substr(getCurrentPage(), 1));
}

function cookieOrDefault(string $cookie, string $default, bool $setIfNull): string
{
    if (isset($_COOKIE[$cookie])) {
        return $_COOKIE[$cookie];
    } else {
        if ($setIfNull) {
            setcookie($cookie, $default);
        }
        return $default;
    }
}