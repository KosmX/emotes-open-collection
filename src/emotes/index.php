<?php declare(strict_types=1);

namespace emotes;

use elements\AlertTag;
use elements\IElement;
use elements\LiteralElement;
use elements\PageElement;
use elements\SimpleList;
use java\EmoteDaemonClient;
use pageUtils\UserHelper;
use routing\Method;
use routing\Router;
use routing\Routes;

class index
{
    public static function index(): object
    {
        $R = new Router(1);

        $R->get(Router::$EMPTY)->action(function () {return self::emoteList();});

        $R->all('~^new$~')->action(function () {return self::uploadEmote();});
        $R->get('~^\\d+\\/icon$~')->action(function () {return self::getIcon();});
        $R->all('~^\\d+(\\/)?$~')->action(function () {return self::displaySingleEmote();});
        $R->all('~^\\d+\\/edit(\\/)?$~')->action(function () {return self::edit();});
        $R->all('~^\\d+\\/delete(\\/)?$~')->action(function () {return self::delete();});
        $R->all('~^\\d+\\/bin(\\/)?$~')->action(function () {return self::bin();});
        $R->all('~^\\d+\\/json(\\/)?$~')->action(function () {return self::json();});
        $R->all('~^\\d+\\/embed.json(\\/)?$~')->action(function () {return self::embed();});

        $R->all('~^my(\\/)?$~')->action(function () {return self::userEmotes();});
        $R->all('~^tmp(\\/)?$~')->action(function () {return self::unpublishedEmotes();});
        $R->all('~^starred(\\/)?$~')->action(function () {return self::starredEmotes();});


        return $R->run(getCurrentPage());
    }


    /**
     * Get emote list with FILTER
     * Uses the $_GET['page'] if available.
     * @param string $filter mysql filter, <b>not protected against sql injection</b><br>
     * emote fields: [id, uuid, emoteOwner, name, description, author, visibility, published]
     * @return IElement Emote list element
     */
    public static function emoteList(string $filter = 'visibility >= 2 && published = true', int $pageSize = 20): IElement {
        $p = (int)($_GET['p']?? 0);
        $p *= $pageSize;

        //this is not very manageable
        if (isset($_GET['from'])) {
            $owner = (int)$_GET['from'];
            if (isset($_GET['s'])) {
                $s = "%{$_GET['s']}%";
                $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where ($filter) && (name like ? or description like ?) && emoteOwner = ? limit ? OFFSET ?;");
                $qr = getDB()->prepare("SELECT COUNT(id) as count FROM emotes where ($filter) && (name like ? or description like ?) && emoteOwner = ?;");
                $q->bind_param('ssiii', $s, $s, $owner, $pageSize, $p);
                $qr->bind_param('ssi', $s, $s, $owner);

            } else {
                $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where ($filter) && emoteOwner = ? limit ? OFFSET ?;");
                $qr = getDB()->prepare("SELECT COUNT(id) as count  FROM emotes where ($filter) && emoteOwner = ?;");
                $q->bind_param('iii', $owner, $pageSize, $p);
                $qr->bind_param('i', $owner);
            }
        } else {
            if (isset($_GET['s'])){
                $s = "%{$_GET['s']}%";
                $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where ($filter) && (name like ? or description like ?) limit ? OFFSET ?;");
                $qr = getDB()->prepare("SELECT COUNT(id) as count  FROM emotes where ($filter) && (name like ? or description like ?);");
                $q->bind_param('ssii', $s, $s, $pageSize, $p);
                $qr->bind_param('ss', $s, $s);
            } else {
                $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where $filter limit ? OFFSET ?;");
                $qr = getDB()->prepare("SELECT COUNT(id) as count FROM emotes where $filter;");
                $q->bind_param('ii', $pageSize, $p);
            }
        }
        $q->execute();
        $result = $q->get_result();
        $qr->execute();
        $count = $qr->get_result()->fetch_array()['count'];
        //var_dump($count);
        $list = self::createEmoteListElement($result);

        $list->addElement(\utils\getPageButtons($count, $p/$pageSize, $pageSize));
        return $list;
    }

    /**
     * Get emote list with FILTER
     * Uses the $_GET['page'] if available.
     * @param string $filter mysql filter, <b>not protected against sql injection</b><br>
     * emote fields: [id, uuid, emoteOwner, name, description, author, visibility, published]
     * @return IElement Emote list element
     */
    public static function searchedEmoteList(string $search, string $filter = 'visibility >= 2', int $pageSize = 20): IElement {
        $p = (int)($_GET['p']?? 0);
        $p *= $pageSize;
        $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where ($filter) && (name like ? or description like ?) limit ? OFFSET ?"); //or emoteOwner = ? and published = true
        $q->bind_param('ii', $pageSize, $p);
        $q->execute();
        $result = $q->get_result();
        return self::createEmoteListElement($result);
    }



    private static function createEmoteListElement(\mysqli_result $list): SimpleList
    {
        $emotes = array();
        foreach ($list as $item) {
            $emote = new Emote($item);
            $emotes[] = $emote;
        }

        $list = new SimpleList();

        $page = getCurrentPage();
        $userButton = '';
        $currentSearch = $_GET['s']?? '';
        if (isset($_GET['from']) && ($user = UserHelper::getUserFromID((int)$_GET['from'])) != null) {
            $userButton = "<input type='hidden' name='from' value='$_GET[from]'>";

            $list->addElement(new LiteralElement(<<<END
<form method="get" target="$page">
<input type="hidden" name="s" value="$currentSearch">
<button type="submit" class="btn btn-light"><i class="bi bi-x-lg"></i> from: $user->displayName</button>
</form>
END));
        }

        $list->addElement(new LiteralElement(<<<END
      <form class="d-flex" method="get" action="$page">
        <input class="form-control me-2" type="search" name="s" placeholder="Search Emote" aria-label="Search" value="$currentSearch">$userButton
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
        <hr>
END));

        foreach ($emotes as $emote) {
            $list->addElement($emote->getCard());
        }

        return $list;
    }

    private static function edit(): Routes|IElement
    {
        /** @var Emote $emote */
        $emote = Emote::get((int)getUrlArray()[1]);
        if (Emote::canViewEmote($emote)) {
            if (UserHelper::getCurrentUser() != null && $emote->ownerID == UserHelper::getCurrentUser()->userID) {
                $list = new SimpleList();

                if (Method::POST->isActive()) {
                    $r = $emote->processEdit();
                    if ($r === true) {
                        $list->addElement(new AlertTag(new LiteralElement("Saved"), 'alert-success'));
                    }
                    else if (is_string($r)) {
                        $list->addElement(new AlertTag(new LiteralElement($r)));
                    }
                    else {
                        $list->addElement(new AlertTag(new LiteralElement("Unknown error, please check your input and try again!")));
                    }
                }

                $t = $emote->published ? "Save" : "Publish!";
                $list->addElement($emote->getEdit("edit", $t));
                return $list;
            }
            return Routes::NOT_FOUND; //TODO use forbidden
        }
        return Routes::NOT_FOUND;
    }

    private static function uploadEmote(): IElement|Routes
    {
        if (UserHelper::getCurrentUser() === null) return Routes::NOT_FOUND;
        $element = new SimpleList();
        if (Method::POST->isActive() && isset($_FILES['emote'])) {
            try {
                $emote = Emote::addEmote($_FILES['emote']);
            } catch (\mysqli_sql_exception $e) {
                $element->addElement(new AlertTag(new LiteralElement("Emote with this UUID already exists.")));
                $emote = null;
            }
            if ($emote === null) {
                $element->addElement(new AlertTag(new LiteralElement("Emote upload failed, please check your file before re-uploading")));
            } else {
                redirect("/e/$emote->id/edit");
            }
        }
        $element->addElement(new LiteralElement(<<<END
<form method="post" target="/e/new" enctype="multipart/form-data"> <!--File upload needs different form-->
    <div class="mb-3">
        <label for="file" class="form-label">Upload an emote</label>
        <input type="file" name="emote" class="form-control" id="emote" aria-labelledby="fileUploadHelp" required>
        <div id="fileUploadHelp" class="form-text">After uploading you can review the emote before publishing.</div>
    </div>
    <button type="submit" class="btn btn-primary">Upload!</button>
</form>
END
        ));
        return $element;

    }

    private static function getIcon(): Routes
    {
        $id = (int)getUrlArray()[1];
        $emote = Emote::get($id);
        if ($emote != null && ($emote->visibility >= 1 || UserHelper::getCurrentUser() != null && $emote->ownerID == UserHelper::getCurrentUser()->userID)) {

            $formatter = new EmoteDaemonClient();
            $q = getDB()->prepare('SELECT data FROM emotes where id = ?');
            $q->bind_param('i', $emote->id);
            $q->execute();
            $r = $q->get_result();

            $formatter->addData($r->fetch_array()['data'], 1);
            $r = $formatter->exchange(array(3));
            if (strlen($r[0]['data']) != 0) {
                header("content-type: image/png");
                echo $r[0]['data'];
                return Routes::SELF_SERVED;
            }

        }
        header("content-type: image/svg+xml");
        echo <<<END
<svg class="bd-placeholder-img img-fluid rounded-start" width="240" height="240" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Image" preserveAspectRatio="xMidYMid slice" focusable="false"><title>Placeholder</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">Image</text></svg>
END;
        return Routes::SELF_SERVED;

    }

    private static function displaySingleEmote(): Routes|IElement
    {
        $emote = Emote::get((int)getUrlArray()[1]);
        //The emote exists && (is not private || I'm the owner)
        if ($emote != null && ($emote->visibility >= 1 || UserHelper::getCurrentUser() != null && $emote->ownerID == UserHelper::getCurrentUser()->userID)) {

            $q = getDB()->prepare("SELECT COUNT(l.userID) as likes FROM emotes join likes l on emotes.id = l.emoteID where emoteID = ?;");
            $q->bind_param('i', $emote->id);
            $q->execute();
            $likes = $q->get_result()->fetch_array()['likes'];

            $q = getDB()->prepare("SELECT u.displayName as displayName, u.username as user FROM emotes as e join users as u on e.emoteOwner = u.id where e.id = ?;");
            $q->bind_param('i', $emote->id);
            $q->execute();
            $r = $q->get_result()->fetch_array();


            $editButton = '';
            if (UserHelper::getCurrentUser() != null && UserHelper::getCurrentUser()->userID == $emote->ownerID) {
                $editButton .= <<<END
<a href="$emote->id/edit" type="button" class="btn btn-primary" style="margin-top: 24px"><i class="bi bi-pencil-square"></i> Edit</a>
<a href="$emote->id/delete" type="button" class="btn btn-danger" style="margin-top: 24px"><i class="bi bi-trash"></i> Delete</a>
END;
            }
            $button = "<button type='submit' class='btn btn-info' disabled>$likes <i class='bi bi-star'></i> Star</button>";
            if (UserHelper::getCurrentUser() != null) {
                $userID = UserHelper::getCurrentUser()->userID;
                $q = getDB()->prepare("SELECT COUNT(userID) as 'liked' FROM likes as l join users u on u.id = l.userID where userID = ? && l.emoteID = ?;");
                $q->bind_param('ii', $userID, $emote->id);
                $q->execute();
                $liked = $q->get_result()->fetch_array()['liked'] != 0;

                if (isset($_POST['like'])) {
                    getDB()->begin_transaction();
                    if ($liked) {
                        $q = getDB()->prepare("DELETE FROM likes where likes.userID = ? && likes.emoteID = ?;");
                        $q->bind_param('ii', $userID, $emote->id);
                        $q->execute();
                        $liked = false;
                        $likes--;
                    } else {
                        $q = getDB()->prepare("INSERT INTO likes (userID, emoteID) VALUES (?, ?);");
                        $q->bind_param('ii', $userID, $emote->id);
                        $q->execute();
                        $liked = true;
                        $likes++;
                    }
                    getDB()->commit();
                }

                if ($liked) {
                    $button = "<button type='submit' class='btn btn-warning'>$likes <i class='bi bi-star-fill'></i> Starred</button>";
                } else {
                    $button = "<button type='submit' class='btn btn-info'>$likes <i class='bi bi-star'></i> Star</button>";
                }

            }
            $editButton .= <<<END
<form method="post" target="$emote->id"><input type="hidden" name="like" value="1" />$button</form>
END;


            $formatter = new EmoteDaemonClient();
            $q = getDB()->prepare('SELECT data FROM emotes where id = ?');
            $q->bind_param('i', $emote->id);
            $q->execute();
            $ri = $q->get_result();

            $hasIcon = '';
            $formatter->addData($ri->fetch_array()['data'], 1);
            $ri = $formatter->exchange(array(3));


            $author = htmlspecialchars($emote->author);
            $description = htmlspecialchars($emote->description);
            $name = htmlspecialchars($emote->name);
            if (strlen($ri[0]['data']) != 0) {
                $hasIcon = <<<END
<a href="$emote->id/icon" type="button" class="btn btn-light" style="margin-top: 12px" download="$name.png"><i class="bi bi-download"></i> Download PNG</a>
END;

            }

            //Meta processor
            PageElement::$meta = <<<META
    <meta content="$name" property="og:title" />
    <meta content="$description" property="og:description" />
    <meta content="$emote->id" property="og:url" />
    <meta content="$emote->id/icon" property="og:image" />
    <meta name="author" content="$author" />
META;


            return new LiteralElement(<<<END
<div>
<img src="/e/$emote->id/icon" class="rounded float-start" width="240px" alt="emote icon" style="margin-right: 18px">
<span class="float-start">
<h1>$name</h1>
<hr>
<h3>Original author: $author</h3>
<h5>$description</h5>
<br><br>
owner: <a href="/u/{$r['user']}">{$r['displayName']}</a>
</span>
<div class="float-none">
<a href="$emote->id/bin" type="button" class="btn btn-success" style="margin-top: 24px" download="$name.emotecraft"><i class="bi bi-download"></i> Download</a>
$editButton
<hr>
Download as traditional emote json and icon:
<br>
<small class="text-muted">You probably don't need this, but who knows</small>
<br>
<a href="$emote->id/json" type="button" class="btn btn-light" style="margin-top: 12px" download="$name.json"><i class="bi bi-download"></i> Download JSON</a>
$hasIcon
<br>
</div>
</div>

END);
        }
        return Routes::NOT_FOUND;
    }

    private static function delete(): Routes|IElement
    {
        $emote = Emote::get((int)getUrlArray()[1]);
        if ($emote !== null && UserHelper::getCurrentUser() !== null && $emote->ownerID === UserHelper::getCurrentUser()->userID) {
            if (Method::POST->isActive() && isset($_POST['deleteEmote'])) {
                $emote->deleteEmote();
                return new LiteralElement(<<<END
<div class="alert alert-success" role="alert">
Emote deleted!
</div>
END);

            } else {
                $list = new SimpleList();
                $list->addElement($emote->getCard());
                $list->addElement(new LiteralElement(<<<END
<h2>Are you sure, you want to delete this emote?</h2>
<form method="post" action="delete">
  <div class="mb-3 form-check">
    <input name="deleteEmote" type="checkbox" class="form-check-input" id="exampleCheck1" aria-describedby="exampleCheckHelp" required>
    <label class="form-check-label" for="exampleCheck1">I really want to delete this emote</label>
    <div id="exampleCheckHelp" class="form-text">By deleting this emote, the UUID will be freed, you may re-upload it</div>
  </div>
  <button type="submit" class="btn btn-danger">Delete emote</button>
</form>
END
));
                return $list;
            }
        }
        return Routes::NOT_FOUND;
    }

    private static function bin(): Routes
    {
        $emote = Emote::get((int)getUrlArray()[1]);
        if (Emote::canViewEmote($emote)) {
            $q = getDB()->prepare('SELECT data FROM emotes where id = ?');
            $q->bind_param('i', $emote->id);
            $q->execute();
            $r = $q->get_result()->fetch_array()['data'];
            header("content-type: application/octet-stream");
            echo $r;
            return Routes::SELF_SERVED;
        }
        return Routes::NOT_FOUND;
    }

    private static function json(): Routes
    {
        $emote = Emote::get((int)getUrlArray()[1]);
        if (Emote::canViewEmote($emote)) {
            $q = getDB()->prepare('SELECT data FROM emotes where id = ?');
            $q->bind_param('i', $emote->id);
            $q->execute();
            $r = $q->get_result()->fetch_array()['data'];
            $daemon = new EmoteDaemonClient();
            $daemon->addData($r, 1);
            header("content-type: application/json");
            $r = $daemon->exchange(array(2))[0]['data'];
            echo $r;

            return Routes::SELF_SERVED;
        }
        return Routes::NOT_FOUND;
    }

    public static function getStarEmoteFunction(): string
    {

        return <<<END
<script>
function star(target, emoteID) {
    //TODO
}
</script>
END;

    }

    private static function userEmotes(string $filter = 'published = true', int $pageSize = 20): IElement|Routes
    {
        if (UserHelper::getCurrentUser() == null) return Routes::NOT_FOUND;

        $p = (int)($_GET['p'] ?? 0);
        $p *= $pageSize;

        //this is not very manageable
        $owner = UserHelper::getCurrentUser()->userID;
        if (isset($_GET['s'])) {
            $s = "%{$_GET['s']}%";
            $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where ($filter) && (name like ? or description like ?) && emoteOwner = ? limit ? OFFSET ?;");
            $qr = getDB()->prepare("SELECT COUNT(id) as count FROM emotes where ($filter) && (name like ? or description like ?) && emoteOwner = ?;");
            $q->bind_param('ssiii', $s, $s, $owner, $pageSize, $p);
            $qr->bind_param('ssi', $s, $s, $owner);

        } else {
            $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where ($filter) && emoteOwner = ? limit ? OFFSET ?;");
            $qr = getDB()->prepare("SELECT COUNT(id) as count  FROM emotes where ($filter) && emoteOwner = ?;");
            $q->bind_param('iii', $owner, $pageSize, $p);
            $qr->bind_param('i', $owner);
        }
        $q->execute();
        $result = $q->get_result();
        $qr->execute();
        $count = $qr->get_result()->fetch_array()['count'];
        //var_dump($count);
        $list = self::createEmoteListElement($result);

        $list->addElement(\utils\getPageButtons($count, $p / $pageSize, $pageSize));
        return $list;
    }

    private static function unpublishedEmotes()
    {
        return self::userEmotes('published = false');
    }

    private static function starredEmotes(string $filter = 'visibility >= 2 && published = true', int $pageSize = 20): Routes|IElement
    {
        if (UserHelper::getCurrentUser() == null) return Routes::NOT_FOUND;


        $p = (int)($_GET['p']?? 0);
        $p *= $pageSize;

        $userID = UserHelper::getCurrentUser()->userID;
        //this is not very manageable
            if (isset($_GET['s'])){
                $s = "%{$_GET['s']}%";
                $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes join likes l on emotes.id = l.emoteID where ($filter) && (name like ? or description like ?) && l.userID = ? limit ? OFFSET ?;");
                $qr = getDB()->prepare("SELECT COUNT(id) as count  FROM emotes join likes l on emotes.id = l.emoteID where ($filter) && (name like ? or description like ?) && l.userID = ?;");
                $q->bind_param('ssiii', $s, $s,$userID, $pageSize, $p);
                $qr->bind_param('ssi', $s, $s, $userID);
            } else {
                $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes join likes l on emotes.id = l.emoteID where ($filter) && l.userID = ? limit ? OFFSET ?;");
                $qr = getDB()->prepare("SELECT COUNT(id) as count FROM emotes join likes l on emotes.id = l.emoteID where ($filter) && l.userID = ?;");
                $q->bind_param('iii', $userID, $pageSize, $p);
                $qr->bind_param('i', $userID);
            }

        $q->execute();
        $result = $q->get_result();
        $qr->execute();
        $count = $qr->get_result()->fetch_array()['count'];
        //var_dump($count);
        $list = self::createEmoteListElement($result);

        $list->addElement(\utils\getPageButtons($count, $p/$pageSize, $pageSize));
        return $list;
    }

    private static function embed(): Routes
    {
        $emote = Emote::get((int)getUrlArray()[1]);
        if (Emote::canViewEmote($emote)) {


            header("content-type: application/json");
            echo <<<END
{
  "title": "$emote->name",
  "description": "$emote->description",
  "url": "https://emotes.kosmx.dev/e/$emote->id",
  "color": 121212,
  "image": {
    "url": "https://emotes.kosmx.dev/e/$emote->id/icon"
  },
  "author": {
    "name": "$emote->author",
    "url": "https://discordapp.com"
  }
}
END;
            return Routes::SELF_SERVED;


        }
        return Routes::NOT_FOUND;
    }
}