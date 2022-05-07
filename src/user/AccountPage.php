<?php
namespace user;

use elements\LiteralElement;
use routing\Router;
use routing\Routes;

/**
 * Static class just for the Autoloader!
 */
class AccountPage
{
    static function getPage(): ?object
    {
        $array = getUrlArray();
        /*
        if (sizeof($array) == 1) {
            return self::currentUser();
        } else if (sizeof($array) == 2) {
            //TODO Display public user page
        } else {
            return Routes::NOT_FOUND;
        }
        return null;
        */

        $R = new Router(1);

        $R->all(Router::$EMPTY)->action(function () {return self::userOverview();});
        $R->all('~^auth\\/[^\\/]+$~')->action(function () {return self::userOverview();});

        $R->all('~^register(\\/|$)~')->action(function () {return self::registerUser();});

        #$R->get('~^oauth\\/~')->action(function () {return self::registerUser();});

        //$R->all(`~%~`);
        return $R->run(getCurrentPage());
    }

    static function userOverview(): ?object
    {
        //echo 'asd';
        if (isset($_SESSION['user'])) {
            return new LiteralElement("User page TODO");
        } else {
            return self::registerUser();
        }
    }

    static function registerUser(): ?object
    {
        if (isset($_SESSION['registration'])) {
            $registration = unserialize($_SESSION['registration']);
        } else {
            $registration = new RegisterUser();
        }
        $ret = $registration->continue();

        $_SESSION['registration'] = serialize($registration);

        return $ret;
        //return null;
    }

}