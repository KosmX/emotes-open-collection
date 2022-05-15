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

    public static function emoteList(): ?IElement {
        $q = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published FROM emotes where visibility >= 2 limit 100"); //or emoteOwner = ? and published = true
        $q->execute();

        $result = $q->get_result();
        $emotes = array();
        foreach ($result as $item) {
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
        <label for="fileUpload" class="form-label">Upload an emote</label>
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