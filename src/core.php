<?php

/** This is CORE.php by KosmX
 *  Some very handy php funcion and DB manager.
 *  The dbSupplier will connect to a database, and allow anyone from the PHP to use the connection
 *  And with its destructor, it will auto-disconnect!
 */

class dbSupplier
{
    private function connectToDB(): mysqli
    {
        return new mysqli($_ENV['db_host'], $_ENV['db_user'], $_ENV['db_password'], 'animals');
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

function printPage(string $title, string $content, $css): void
{
    $cssRow = "";
    if (isset($css)) {
        $cssRow = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\">";
    }
    echo <<<END
<!DOCTYPE html>
<html>
    <head>
        <title>$title</title>
        $cssRow
    </head>
    <body>
    $content
    </body>
</html>
END;
}
