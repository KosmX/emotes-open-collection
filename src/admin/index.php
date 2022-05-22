<?php declare(strict_types=1);

namespace admin;

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
 * If user is not logged in OR has no privileges, it will return 403.
 * No more verification is required in sub-methods
 */
class index
{
    public static function index(): ?object
    {
        if (UserHelper::getCurrentUser() != null && UserHelper::getCurrentUser()->privileges >= 2) {

            $R = new Router(1);

            $R->all('~^t\\/[^\\/]+(\\/)?$~')->action(function () {
                return self::editTable(getUrlArray()[2]);
            });
            $R->all('~^e\\/\\d+(\\/)?$~')->action(function () {
                return self::editEmote((int)getUrlArray()[2]);
            });
            $R->all('~^u\\/[^\\/]+(\\/)?$~')->action(function () {
                return self::editUser(getUrlArray()[2]);
            });

            $R->get(Router::$EMPTY)->action(function () {return self::adminMenu();});

            return $R->run(getCurrentPage());

        }
        return Routes::FORBIDDEN;
    }

    private static function editTable(string $table): IElement|Router {

        $list = new SimpleList();
        $list->addElement(new LiteralElement("<h1>Edit: $table</h1>"));

        $table = new Table($table);


        if (Method::POST->isActive()) {
            $table->post();
        }

        $list->addElement($table);

        return $list;


    }

    private static function adminMenu(): IElement
    {

        $list = new SimpleList();
        $q = getDB()->prepare("SHOW TABLES");
        $q->execute();

        $tables = '';
        foreach ($q->get_result() as $item) {
            foreach ($item as $table) {
                $tables .= <<<END
<a class="btn btn-primary" href="/admin/t/$table" role="button">$table</a>

END;
            }
        }

        $list->addElement(new LiteralElement(<<<END
<h1>Tables:</h1>
<hr>
$tables
END));

        return $list;
    }

    private static function editEmote(int $emoteID) {

        $emote = Emote::get($emoteID);

        $list = new SimpleList();

        if ($emote != null) {
            if (Method::POST->isActive()) {
                $r = $emote->processEdit();
                if ($r === true) {
                    $list->addElement(new AlertTag(new LiteralElement("Saved"), 'alert-success'));
                } else if (is_string($r)) {
                    $list->addElement(new AlertTag(new LiteralElement($r)));
                } else {
                    $list->addElement(new AlertTag(new LiteralElement("Unknown error, please check your input and try again!")));
                }
            }

            $t = $emote->published ? "Save" : "Publish!";
            $list->addElement($emote->getEdit("edit", $t));
            return $list;
        }
        return Routes::NOT_FOUND;
    }

    private static function editUser(string $userName): IElement|Routes
    {

        $user = UserHelper::getUser($userName);
        if ($user == null) return Routes::NOT_FOUND;



        $elements = new SimpleList();


        if (Method::POST->isActive()) {
            if (isset($_POST['delete'])) {
                $user->deleteUserQuery();
            } else {
                if ($user->updateProfile()) {
                    $elements->addElement(new AlertTag(new LiteralElement("Successfully saved"), 'alert-success'));
                }
            }
        }


        $elements->addElement(new LiteralElement("<h1>Edit profile</h1>"));
        $elements->addElement($user->getForm($userName));

        $elements->addElement(new LiteralElement("<hr>"));

        $elements->addElement(new LiteralElement(<<<END
<form method="post">
  <input name="delete" type="hidden" value="1">
  <button type="submit" class="btn btn-danger">Delete user</button>
</form>
END
));

        #$_SESSION['profEdit'] = serialize($user);

        return $elements;
    }
}