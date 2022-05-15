<?php declare(strict_types=1);

namespace emotes;

use elements\AlertTag;
use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
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

        return $R->run(getCurrentPage());
    }

    public static function emoteList(): ?IElement {
        return new LiteralElement("TODO");
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
}