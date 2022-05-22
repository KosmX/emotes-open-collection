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
use function utils\getPageButtons;

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
            $R->get('~^e(\\/)?$~')->action(function () {
                return self::listEmotes();
            });
            
            $R->all('~^u\\/[^\\/]+(\\/)?$~')->action(function () {
                return self::editUser(getUrlArray()[2]);
            });
            $R->get('~^u(\\/)?$~')->action(function () {
                return self::listUsers();
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
<hr>
<a href="admin/u" class="btn btn-primary">Users</a>
<hr>
<a href="admin/e" class="btn btn-primary">Emotes</a>
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
            $list->addElement($emote->getEdit("$emoteID", $t));
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

    private static function listUsers(): Routes|IElement
    {
        $p = (int)($_GET['p']?? 0);

        $q = getDB()->prepare("SELECT COUNT(*) as count FROM users");
        $q->execute();
        $count = $q->get_result()->fetch_array()['count'];

        $q = getDB()->prepare("SELECT id, username, displayName, email, isEmailPublic, theCheckbox FROM users LIMIT 64 OFFSET ?");
        $q->bind_param('i', $p);
        $q->execute();
        $r = $q->get_result();

        $rows = '';
        foreach ($r as $item) {

            $rows .= <<<END
<tr>
<td>$item[id]</td>
<td>$item[username]</td>
<td>$item[displayName]</td>
<td>$item[email]</td>
<td>$item[isEmailPublic]</td>
<td>$item[theCheckbox]</td>
<td><a href="u/$item[username]" class="btn btn-primary"><i class="bi bi-pencil-square"></i></a></td>
</tr>
END;}


        $paginator = getPageButtons($count, $p, 64)->build();
        return new LiteralElement(<<<END
<div>
<h1>Users</h1>
<table class="table">
<tr>
<th>id</th>
<th>username</th>
<th>display name</th>
<th>email</th>
<th>is email public</th>
<th>the Checkbox</th>
<th>edit</th>
</tr>
$rows
</table>
$paginator
</div>
END);
    }

    private static function listEmotes()
    {
        $p = (int)($_GET['p']?? 0);

        $q = getDB()->prepare("SELECT COUNT(*) as count FROM emotes");
        $q->execute();
        $count = $q->get_result()->fetch_array()['count'];

        $q = getDB()->prepare("SELECT id, name, description, author, emoteOwner, visibility, published FROM emotes LIMIT 64 OFFSET ?");
        $q->bind_param('i', $p);
        $q->execute();
        $r = $q->get_result();

        $rows = '';
        foreach ($r as $item) {
            $name = htmlspecialchars($item['name']);
            $description = htmlspecialchars($item['description']);
            $author = htmlspecialchars($item['author']);
            $rows .= <<<END
<tr>
<td>$item[id]</td>
<td>$name</td>
<td>$description</td>
<td>$author</td>
<td>$item[emoteOwner]</td>
<td>$item[visibility]</td>
<td>$item[published]</td>
<td><a href="e/$item[id]" class="btn btn-primary"><i class="bi bi-pencil-square"></i></a></td>
</tr>
END;}


        $paginator = getPageButtons($count, $p, 64)->build();
        return new LiteralElement(<<<END
<div>
<h1>Emotes</h1>
<table class="table">
<tr>
<th>id</th>
<th>name</th>
<th>description</th>
<th>author</th>
<th>ownerID</th>
<th>visibility</th>
<th>published</th>
<th>edit</th>
</tr>
$rows
</table>
$paginator
</div>
END);
    }
}