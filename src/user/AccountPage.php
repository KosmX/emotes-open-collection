<?php
namespace user;

use elements\AlertTag;
use elements\bootstrap\Button;
use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
use emotes\Emote;
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
        $cookieTest = \Cookies::testCookies();
        if ($cookieTest != null) return $cookieTest;
        $R = new Router(1);

        $R->all(Router::$EMPTY)->action(function () {return self::userOverview();});
        $R->all('~^auth\\/[^\\/]+$~')->action(function () {return self::userOverview();});


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

        if (UserHelper::getCurrentUser() === null) {
            $_SESSION['registration'] = serialize($registration);
        } else {
            unset($_SESSION['registration']);
        }

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

        $q = getDB()->prepare("SELECT COUNT(DISTINCT e.id) as 'emotes', COUNT(DISTINCT l.emoteID) as 'likes' from users as u left join likes l on u.id = l.userID left join emotes e on u.id = e.emoteOwner where u.id = ? group by userID;");
        $q->bind_param('i', $user->userID);
        $q->execute();
        $r = $q->get_result()->fetch_array();

        $buttonText = $user->displayName;
        $lastChar = substr($user->displayName, -1);
        if ($lastChar == 's' || $lastChar == 'S') {
            $buttonText .= '\' '; //The names last char is s, we use 's differently
        } else {
            $buttonText .= '\'s ';
        }
        $buttonText .= "emotes ($r[emotes])";

        $elements->addElement(new LiteralElement(<<<END
<h1>$user->displayName</h1>
<h5>$emailField</h5>
<br>
<i class="bi bi-star-fill"></i> Starred: $r[likes]
<hr>
<form method="get" action="/e">
<input type="hidden" name="from" value="$user->userID">
<button type="submit" class="btn btn-info">$buttonText</button>
</form>

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

        $elements->addElement(new LiteralElement("<hr>"));

        $elements->addElement(new Button('/settings/delete', new LiteralElement("Delete user"), 'danger'));

        #$_SESSION['profEdit'] = serialize($user);

        return $elements;
    }

    public static function deleteUser(): IElement|Routes
    {
        if (UserHelper::getCurrentUser() == null) return Routes::NOT_FOUND;
        $elements = new SimpleList();

        if (Method::POST->isActive() && isset($_POST['checkbox'])) {
            UserHelper::getCurrentUser()->deleteUser();
            redirect('/');
            return \index_page::getIndex();

        }

        $elements->addElement(new LiteralElement(<<<END

<form method="post" action="/settings/delete">
  <div class="mb-3 form-check">
    <input name="checkbox" type="checkbox" class="form-check-input" id="exampleCheck1" aria-describedby="exampleCheckHelp" required>
    <label class="form-check-label" for="exampleCheck1">I want to delete my account</label>
    <div id="exampleCheckHelp" class="form-text">By deleting your account, every information will be permanently removed.</div>
  </div>
  <button type="submit" class="btn btn-danger">Delete my account</button>
</form>
END));

        return $elements;
    }

    public static function themes(): Routes|IElement
    {
        if (UserHelper::getCurrentUser() != null) {
            $list = new SimpleList();

            if (isset($_POST['theme'])) {
                $theme = (int)$_POST['theme'];
                if ($theme < sizeof(UserHelper::$themes)) {
                    UserHelper::getCurrentUser()->theme = $theme;

                    getDB()->begin_transaction();
                    $userID = UserHelper::getCurrentUser()->userID;
                    $q = getDB()->prepare('UPDATE users SET theme = ? where id = ?');
                    $q->bind_param('ii', $theme, $userID);
                    $q->execute();
                    getDB()->commit();

                } else {
                    $list->addElement(new AlertTag(new LiteralElement("Selected theme is invalid")));
                }

            }
            $options = '';
            $i = 0;
            foreach (UserHelper::$themes as $theme) {
                $options .= Emote::option($i, $theme[0], UserHelper::getCurrentUser()->theme);
                $i++;
            }


            $list->addElement(new LiteralElement(<<<END
<form action="themes" method="post">
<select class="form-select" aria-label="Select theme" name="theme">
    $options
</select>
<hr>
<button type="submit" class="btn btn-success">Select theme!</button>
</form>
END
));

            return $list;
        }

        return Routes::FORBIDDEN;
    }

}