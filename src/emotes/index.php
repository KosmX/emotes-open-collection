<?php declare(strict_types=1);

namespace emotes;

use elements\AlertTag;
use elements\IElement;
use elements\LiteralElement;
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

        return $R->run(getCurrentPage());
    }

    public static function getUserEmoteList(): ?IElement {
        $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where visibility >= 2 limit 100"); //or emoteOwner = ? and published = true
        $q->execute();
        $result = $q->get_result();
        return self::createEmoteListElement($result->fetch_all());
    }

    /**
     * Get emote list with FILTER
     * Uses the $_GET['page'] if available.
     * @param string $filter mysql filter, <b>not protected against sql injection</b><br>
     * emote fields: [id, uuid, emoteOwner, name, description, author, visibility, published]
     * @return IElement Emote list element
     */
    public static function emoteList(string $filter = 'visibility >= 2', int $pageSize = 20): IElement {
        $p = (int)($_GET['page']?? 0);
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

        $list->addElement(self::getPageButtons($count, $p/$pageSize, $pageSize));
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
        $p = (int)($_GET['page']?? 0);
        $p *= $pageSize;
        $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where ($filter) && (name like ? or description like ?) limit ? OFFSET ?"); //or emoteOwner = ? and published = true
        $q->bind_param('ii', $pageSize, $p);
        $q->execute();
        $result = $q->get_result();
        return self::createEmoteListElement($result);
    }


    public static function getPageButtons(int $length, int $currentPage, int $pageSize = 20): IElement
    {
        $length = min($length, 1);
        $maxPage = ceil($length/$pageSize);
        $currentPage = min($maxPage - 1, $currentPage); //before something bad happens
        $before = min(2, $currentPage);
        $after = min(2, $maxPage - $currentPage - 1);

        $pages = '';
        if ($currentPage > 2) {
            $pages .= <<<END
    <li class="page-item disabled">
      <span class="page-link">...</span>
    </li>
END;
        }
        $currentUrl = $_SERVER['HTTP_HOST'].getCurrentPage().'?';
        if (isset($_GET['from'])) {
            $currentUrl .= "from={$_GET['from']}&";
        }
        if (isset($_GET['s'])) {
            $currentUrl .= "s={$_GET['s']}&";
        }

        for ($i = 0; $i < $before; $i++) {
            $index = $currentPage - $before + $i;
            $iPlus = $index + 1;
            $pages .= <<<END
    <li class="page-item">
      <a href="/{$currentUrl}p=$index" class="page-link">$iPlus</a>
    </li>
END;
        }

        $iPlus = $currentPage + 1;
        $pages .= <<<END
    <li class="page-item active">
      <span class="page-link">
        $iPlus
      </span>
    </li>
END;

        for ($i = 0; $i < $after; $i++) {
            $index = $currentPage + $i;
            $iPlus = $index + 1;
            $pages .= <<<END
    <li class="page-item">
      <a href="/{$currentUrl}p=$index" class="page-link">$iPlus</a>
    </li>
END;
        }

        return new LiteralElement(<<<END
<nav aria-label="Page navigation example">
  <ul class="pagination justify-content-center">
    $pages
  </ul>
</nav>
END
);
    }

    private static function createEmoteListElement(\mysqli_result $list): SimpleList
    {
        $emotes = array();
        foreach ($list as $item) {
            $emote = new Emote($item);
            $emotes[] = $emote;
        }

        $list = new SimpleList();

        foreach ($emotes as $emote) {
            $list->addElement($emote->getCard());
        }

        return $list;
    }

    private static function edit()
    {
        return new LiteralElement("TODO");
    }

    private static function uploadEmote(): IElement|Routes
    {
        if (UserHelper::getCurrentUser() === null) return Routes::NOT_FOUND;
        $element = new SimpleList();
        if (Method::POST->isActive() && isset($_FILES['emote'])) {
            $emote = Emote::addEmote($_FILES['emote']);
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
}