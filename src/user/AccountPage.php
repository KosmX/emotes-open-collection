<?php
namespace user;

use routing\Routes;

/**
 * Static class just for the Autoloader!
 */
class AccountPage
{
    static function getPage(): ?object
    {
        $array = getUrlArray();
        if (sizeof($array) == 1) {
            return self::currentUser();
        } else if (sizeof($array) == 2) {
            //TODO Display public user page
        } else {
            return Routes::NOT_FOUND;
        }
        return null;
    }

    static function currentUser(): ?object
    {
        if (isset($_SESSION['user'])) {
            //TODO USER PROFILE PAGE
        } else {
            if (false && isset($_SESSION['registration'])) {
                $registration = unserialize($_SESSION['registration']);
            } else {
                $registration = new RegisterUser();
            }
            $ret = $registration->continue();

            $_SESSION['registration'] = serialize($registration);

            return $ret;
        }
        return null;
    }

}