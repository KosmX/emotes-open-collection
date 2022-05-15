<?php
namespace user;

use elements\AlertTag;
use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
use pageUtils\UserHelper;
use routing\Method;
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

        //$R->all('~^register(\\/|$)~')->action(function () {return self::registerUser();});


        #$R->get('~^oauth\\/~')->action(function () {return self::registerUser();});

        //$R->all(`~%~`);
        return $R->run(getCurrentPage());
    }

    static function userOverview(): ?object
    {
        //echo 'asd';
        if (isset($_SESSION['user'])) {
            return self::getAccountPage();
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

    /**
     * @return IElement|Routes
     */
    public static function getAccountPage(?UserHelper $user = null): ?object {
        if ($user == null) {
            $R = new Router(1);
            $R->all(Router::$EMPTY)->action(function () {
                return UserHelper::getCurrentUser();
            });
            $R->all('~.*~')->action(function () {
                return UserHelper::getUser(getUrlArray()[1]);

            });
            /** @var UserHelper $user */
            $user = $R->run(getCurrentPage());
            if ($user == null) {
                return Routes::NOT_FOUND;
            }
        }

        $elements = new SimpleList();

        $emailField = '';
        if ($user->publicEmail || UserHelper::getCurrentUser() !== null && $user->userID === UserHelper::getCurrentUser()->userID) {
            $emailField = $user->email;
        }

        $elements->addElement(new LiteralElement(<<<END
<h1>$user->displayName</h1>
$emailField
END
));

        return $elements;
    }

    public static function logout(): IElement|Routes
    {
        if (UserHelper::getCurrentUser() == null) {
            return Routes::NOT_FOUND;
        } else {
            UserHelper::logout();
            return new LiteralElement("<h2>Goodbye!</h2>");
        }
    }

    public static function userSettings(): IElement|Routes
    {
        if (UserHelper::getCurrentUser() == null) return Routes::NOT_FOUND;

        $user = clone(UserHelper::getCurrentUser());


        $elements = new SimpleList();
        if (Method::POST->isActive()) {
            if($user->updateProfile()) {
                $elements->addElement( new AlertTag(new LiteralElement("Successfully saved"), 'alert-success'));
            }
        }


        $elements->addElement(new LiteralElement("<h1>Edit profile</h1>"));
        $elements->addElement($user->getForm('/settings/profile'));

        $_SESSION['profEdit'] = serialize($user);

        return $elements;
    }

}