<?php declare(strict_types=1);

namespace emotes;

use elements\AlertTag;
use elements\IElement;
use elements\LiteralElement;
use elements\PageElement;
use elements\SimpleList;
use i18n\Translatable;
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
        $R->all('~^\\d+(\\/)?$~')->action(function () {return self::displaySingleEmote();});
        $R->all('~^\\d+\\/edit(\\/)?$~')->action(function () {return self::edit();});
        $R->all('~^\\d+\\/delete(\\/)?$~')->action(function () {return self::delete();});

        $R->get('~^\\d+\\/icon$~')->action(function () {return self::getIcon();});
        $R->get('~^\\d+\\/bin(\\/)?$~')->action(function () {return self::bin();});
        $R->get('~^\\d+\\/json(\\/)?$~')->action(function () {return self::json();});
        $R->get('~^\\d+\\/embed\\.json(\\/)?$~')->action(function () {return self::embed();});
        $R->get('~^\\d+\\/icon\\.png$~')->action(function () {return self::getIcon(false);});

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

            $from = Translatable::getTranslated("emotes.from", array("user"=>$user->displayName));

            $list->addElement(new LiteralElement(<<<END
<form method="get" action="$page">
<input type="hidden" name="s" value="$currentSearch">
<button type="submit" class="btn btn-light"><i class="bi bi-x-lg"></i> $from</button>
</form>
END));
        }
        $search = Translatable::getTranslated("emotes.search");
        $searchPlaceholder = Translatable::getTranslated("emotes.search.text");

        $list->addElement(new LiteralElement(<<<END
      <form class="d-flex" method="get" action="$page">
        <input class="form-control me-2" type="search" name="s" placeholder="$searchPlaceholder" aria-label="Search" value="$currentSearch">$userButton
        <button class="btn btn-outline-success" type="submit">$search</button>
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
                        $list->addElement(new AlertTag(new Translatable("saved"), 'alert-success'));
                    }
                    else if (is_string($r)) {
                        $list->addElement(new AlertTag(new LiteralElement($r)));
                    }
                    else {
                        $list->addElement(new AlertTag(new Translatable("unknown_error")));
                    }
                }

                $t = $emote->published ? Translatable::getTranslated("save") : Translatable::getTranslated("publish");
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
                $element->addElement(new AlertTag(new Translatable("UUID_exists")));
                $emote = null;
            }
            if ($emote === null) {
                $element->addElement(new AlertTag(new Translatable("upload_failed")));
            } else {
                redirect("/e/$emote->id/edit");
            }
        }

        $upload = Translatable::getTranslated("emotes.upload");
        $helper = Translatable::getTranslated("emotes.upload.helper");
        $uploadButton = Translatable::getTranslated("emotes.upload.button");

        $element->addElement(new LiteralElement(<<<END
<form method="post" action="/e/new" enctype="multipart/form-data"> <!--File upload needs different form-->
    <div class="mb-3">
        <label for="file" class="form-label">$upload</label>
        <input type="file" name="emote" class="form-control" id="emote" aria-labelledby="fileUploadHelp" required>
        <div id="fileUploadHelp" class="form-text">$helper</div>
    </div>
    <button type="submit" class="btn btn-primary">$uploadButton</button>
</form>
END
        ));
        return $element;

    }

    private static function getIcon(bool $fallback = true): Routes
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
        if ($fallback) {
            header("content-type: image/svg+xml");
            echo <<<END
<svg class="bd-placeholder-img img-fluid rounded-start" width="240" height="240" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Image" preserveAspectRatio="xMidYMid slice" focusable="false"><title>Placeholder</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">Image</text></svg>
END;
            return Routes::SELF_SERVED;
        }
        return Routes::NOT_FOUND;
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
                $edit = Translatable::getTranslated("emotes.edit");
                $delete = Translatable::getTranslated("emotes.delete");
                $editButton .= <<<END
<a href="$emote->id/edit" type="button" class="btn btn-primary" style="margin-top: 24px"><i class="bi bi-pencil-square"></i> $edit</a>
<a href="$emote->id/delete" type="button" class="btn btn-danger" style="margin-top: 24px"><i class="bi bi-trash"></i> $delete</a>
END;
            }
            $star = Translatable::getTranslated("emotes.star");
            $starred = Translatable::getTranslated("emotes.starred");
            $button = "<button type='submit' class='btn btn-info' disabled>$likes <i class='bi bi-star'></i> $star</button>";
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
                    $button = "<button type='submit' class='btn btn-warning'>$likes <i class='bi bi-star-fill'></i> $starred</button>";
                } else {
                    $button = "<button type='submit' class='btn btn-info'>$likes <i class='bi bi-star'></i> $star</button>";
                }

            }
            $editButton .= <<<END
<form method="post" action="$emote->id"><input type="hidden" name="like" value="1" />$button</form>
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
                $icon = Translatable::getTranslated("emotes.download.png");
                $hasIcon = <<<END
<a href="$emote->id/icon" type="button" class="btn btn-light" style="margin-top: 12px" download="$name.png"><i class="bi bi-download"></i> $icon</a>
END;

            }

            //Meta processor
            PageElement::$meta = <<<META
    <meta content="$name" property="og:title" />
    <meta content="$description" property="og:description" />
    <meta content="https://emotes.kosmx.dev/e/$emote->id/icon.png" property="og:image" />
    <meta name="author" content="$author" />
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#03FCC2">
    <link type="application/json+oembed" href="https://emotes.kosmx.dev/e/$emote->id/embed.json">
META;

            if ($author != '') $author = Translatable::getTranslated("emotes.author", array("author"=>$author));
            $owner = Translatable::getTranslated("emotes.owner", array("user"=>"<a href=\"/u/{$r['user']}\">{$r['displayName']}</a>"));
            $download = Translatable::getTranslated("emotes.download");
            $downloadOther = Translatable::getTranslated("emotes.download.other");
            $downloadNote = Translatable::getTranslated("emotes.download.other.note");
            $downloadJson = Translatable::getTranslated("emotes.download.json");

            return new LiteralElement(<<<END
<div>
<img src="/e/$emote->id/icon" class="rounded float-start" width="240px" alt="emote icon" style="margin-right: 18px">
<span class="float-start">
<h1>$name</h1>
<hr>
<h3>$author</h3>
<h5>$description</h5>
<br><br>
$owner
</span>
<div class="float-none">
<a href="$emote->id/bin" type="button" class="btn btn-success" style="margin-top: 24px" download="$name.emotecraft"><i class="bi bi-download"></i> $download</a>
$editButton
<hr>
$downloadOther
<br>
<small class="text-muted">$downloadNote</small>
<br>
<a href="$emote->id/json" type="button" class="btn btn-light" style="margin-top: 12px" download="$name.json"><i class="bi bi-download"></i> $downloadJson</a>
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
                $emoteDeleted = Translatable::getTranslated("emotes.delete.success");
                return new LiteralElement(<<<END
<div class="alert alert-success" role="alert">
$emoteDeleted
</div>
END);

            } else {
                $list = new SimpleList();
                $list->addElement($emote->getCard());

                $text = Translatable::getTranslated("emotes.delete.page");
                $check = Translatable::getTranslated("emotes.delete.check");
                $note = Translatable::getTranslated("emotes.delete.note");
                $button = Translatable::getTranslated("emotes.delete.button");

                $list->addElement(new LiteralElement(<<<END
<h2>$text</h2>
<form method="post" action="delete">
  <div class="mb-3 form-check">
    <input name="deleteEmote" type="checkbox" class="form-check-input" id="exampleCheck1" aria-describedby="exampleCheckHelp" required>
    <label class="form-check-label" for="exampleCheck1">$check</label>
    <div id="exampleCheckHelp" class="form-text">$note</div>
  </div>
  <button type="submit" class="btn btn-danger">$button</button>
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

    private static function starredEmotes(string $filter = 'visibility >= 1 && published = true', int $pageSize = 20): Routes|IElement
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
   "author_name": "$emote->author",
   "provider_name": "emotes.kosmx.dev",
   "provider_url": "https://emotes.kosmx.dev/"
}
END;
            return Routes::SELF_SERVED;


        }
        return Routes::NOT_FOUND;
    }
}