<?php declare(strict_types=1);

namespace emotes;

use elements\IElement;
use elements\LiteralElement;
use java\EmoteDaemonClient;
use JetBrains\PhpStorm\ArrayShape;
use pageUtils\UserHelper;

class Emote
{
    public int $id;
    public string $uuid;
    public int $ownerID;
    public string $name;
    public string $description;
    public string $author;
    public int $visibility;
    public bool $published;

    /**
     * @param array $data
     * [ArrayShape]()
     */
    public function __construct(
        #[ArrayShape(['id' => "int", 'uuid' => 'string', 'ownerID' => 'int', 'name' => 'string', 'description' => 'string', 'author' => 'string', 'visibility' => 'int', 'published' => 'int'])]
        array $data
    )
    {
        $this->id = $data['id'];
        $this->uuid = $data['uuid'];
        $this->ownerID = $data['ownerID'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->author = $data['author']?? '';
        $this->visibility = $data['visibility'];
        $this->published = $data['published'] != 0;
    }

    #[ArrayShape(['id' => "int", 'uuid' => 'string', 'ownerID' => 'int', 'name' => 'string', 'description' => 'string', 'author' => 'string', 'visibility' => 'int', 'published' => 'int'])]
    private function getDataArray(): array
    {
        return array(
            'id' => $this->id,
            'uuid' => $this->uuid,
            'ownerID' => $this->ownerID,
            'name' => $this->name,
            'description' => $this->description,
            'author' => $this->author,
            'visibility' => $this->visibility,
            'published' => $this->published ? 1 : 0
        );
    }


    /**
     * Get an emote from ID
     * @param int $id emoteID (Not the UUID)
     * @return Emote|null null if not exists
     * Having result does not mean you're allowed to see the content.
     */
    public static function get(int $id): ?Emote
    {
        $query = getDB()->prepare("SELECT id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published from emotes where emotes.id = ?");
        $query->bind_param('i', $id);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows == 1) {
            $emote = new Emote($result->fetch_array());
            if (!$emote->published) {
                $emote->visibility = 2;
            }
            return $emote;
        }
        return null;
    }

    public static function getSelectParams(): string
    {
        return " id, uuid, emoteOwner as 'ownerID', name, description, author, visibility, published ";
    }

    public function getCard(): IElement
    {

        $title = htmlspecialchars($this->name);
        $desc = htmlspecialchars($this->description);
        $author = htmlspecialchars($this->author);

        $button = <<<END
<a href="/e/$this->id/bin" class="btn btn-success" download="$title.emotecraft"><i class="bi bi-download"></i></a>
END;
        if (!$this->published) {
            $button = <<<END
<a href="/e/$this->id/edit" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Publish</a>
END;

        }


        return new LiteralElement(<<<END
<div class="card mb-3" style="max-width: 840px;">
  <div class="row g-0">
    <div class="col-md-4">
      <!--If you know how to fix these image positions, please DM me!-->
      <img src="/e/$this->id/icon" class="img-fluid rounded-start" alt="emote icon" style="object-fit: fill;">
      <!--If you know how to fix these image positions, please DM me!-->
    </div>
    <div class="col-md-8">
      <div class="card-body">
      <a href="/e/$this->id">
        <h5 class="card-title">$title</h5></a>
        <p class="card-text">$desc</p>
        <p class="card-text"><small class="text-muted">$author</small></p>
        
        $button
      </div>
    </div>
  </div>
</div>
END);

    }

    /**
     * Edit emote content, will always result in a POST request
     * @param string $callback callback URL
     * @return IElement HTML content
     */
    public function getEdit(string $callback, string $buttonTitle = "Save"): IElement
    {
        //TODO icon can NOT be deleted, only replaced

        $options = self::option(0, "Private", $this->visibility);
        $options .= self::option(1, "Unlisted", $this->visibility);
        $options .= self::option(2, "Public", $this->visibility);
        $options .= self::option(3, "Public & include in public ZIP", $this->visibility);


        return new LiteralElement(<<<END
<form method="post" id="editform" action="$callback" enctype="multipart/form-data">
  <div class="mb-3">
    <label for="icon" class="form-label">Emote icon:</label>
    <input type="file" class="form-control" name="icon" id="icon" aria-describedby="iconHelp">
  </div>
  <div class="mb-3">
    <label for="name" class="form-label">Emote name:</label>
    <input type="text" class="form-control" name="name" id="name" aria-describedby="nameHelp" value="$this->name" required>
  </div>
  <div class="mb-3">
    <label for="description" class="form-label">Description:</label>
    <input type="text" class="form-control" name="description" id="description" value="$this->description">
  </div>
  <div class="mb-3">
    <label for="author" class="form-label">Author:</label>
    <input type="text" class="form-control" name="author" id="author" value="$this->author">
  </div>
  <div class="mb-3">
    <label for="visibility" class="form-label">Emote visibility</label>
    <select name="visibility" class="form-select" form="editform" aria-label="Select visibility">
    $options
    </select>
  </div>
  <button type="submit" class="btn btn-primary">$buttonTitle</button>
</form>
END);
    }

    public static function option(int $value, string $text, int $selected): string
    {
        if ($selected == $value) {
            return "<option value=\"$value\" selected=\"selected\">$text</option>";
        } else {
            return "<option value=\"$value\">$text</option>";
        }
    }

    /**
     * It does <b>not</b> verify if the user can modify this emote.
     * @return bool|string true if success, string if there is an error message, false if unknown failure
     * It may change itself to not lose data.
     */
    public function processEdit(): bool|string
    {
        $this->name = $_POST['name']?? $this->name;
        $this->description = $_POST['description']?? '';
        $this->author = $_POST['author']?? '';
        $this->visibility = (int)$_POST['visibility'];

        /** @var string|null $icon */
        $icon = null;
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
            $imageData = $_FILES['icon'];
            $type = exif_imagetype($imageData['tmp_name']);
            if ($type === false || $type !== IMAGETYPE_PNG) {
                return "Icon must be PNG";
            }
            $res = getimagesize($imageData['tmp_name']);
            if ($res === false) return "Invalid image";
            if ($res[0] !== $res[1]) return "Image must be a square (same with and height)";

            $icon = file_get_contents($imageData['tmp_name']);
            if ($icon === false) return false;
        }

        getDB()->begin_transaction();

        $getOldData = getDB()->prepare('SELECT data FROM emotes where id = ? limit 1 FOR UPDATE');
        $getOldData->bind_param('i', $this->id);
        $getOldData->execute();
        $r = $getOldData->get_result();
        $r = $r->fetch_array()['data'];

        $emoteService = new EmoteDaemonClient();
        $emoteService->addData($r, 1);
        $emoteService->addData(json_encode($this->getDataArray()), 8);
        if ($icon != null) {
            $emoteService->addData($icon,3);
        }
        $r = $emoteService->exchange(array(1));
        if ($r == null) {
            getDB()->rollback();
            return false;
        }

        $r = $r[0]['data'];

        $query = getDB()->prepare('UPDATE emotes SET name = ?, description = ?, author = ?, published = true, visibility = ?, data = ? WHERE id = ?');
        $NULL = '';
        $query->bind_param('sssibi', $this->name, $this->description, $this->author, $this->visibility, $NULL, $this->id);
        $query->send_long_data(4, $r);
        $query->execute();

        getDB()->commit();

        return true;
    }


    /**
     * Register a newly uploaded emote into the db
     * @param array $uploadedFile
     * @return Emote|null null if failed (invalid emote)
     */
    public static function addEmote(
        #[ArrayShape(['name' => 'string', 'full_path' => 'string', 'type' => 'string', 'tmp_name' => 'string', 'error' => 'int', 'size' => 'int'])]
        array $uploadedFile
    ): ?Emote {
        $tmpArray = explode(".", $uploadedFile['name']);
        $ext = end($tmpArray);
        $type = match ($ext) {
            'emotecraft' => 1,
            'json' => 2,
            default => 0
        };
        if ($type == 0) return null;
        $formatter = new EmoteDaemonClient();
        $formatter->addData(file_get_contents($uploadedFile['tmp_name']), $type);
        $result = $formatter->exchange(array(8, 1));
        if ($result === null || strlen($result[1]['data']) == 0) return null;

        $json = json_decode($result[0]['data'], true);
        $json['ownerID'] = UserHelper::getCurrentUser()->userID;

        getDB()->begin_transaction();
        $addEmoteQuery = getDB()->prepare('INSERT INTO emotes (uuid, emoteOwner, name, description, author, data) VALUES (?, ?, ?, ?, ?, ?)');
        $NULL = '';
        $addEmoteQuery->bind_param('sisssb', $json['uuid'], $json['ownerID'], $json['name'], $json['description'], $json['author'], $NULL);
        $addEmoteQuery->send_long_data(5, $result[1]['data']);
        $addEmoteQuery->execute();
        $emote = self::get($addEmoteQuery->insert_id);
        if ($emote === null) {
            getDB()->rollback();
            return null;
        }
        getDB()->commit();
        return $emote;

    }

    /**
     * Can the emote be displayed
     * @param Emote|null $emote
     * @return bool
     */
    public static function canViewEmote(?Emote $emote): bool
    {
        return $emote != null && ($emote->visibility >= 1 || UserHelper::getCurrentUser() != null && $emote->ownerID == UserHelper::getCurrentUser()->userID);
    }

    /**
     * Delete an emote, does not verify rights
     * @return void
     */
    public function deleteEmote(): void
    {
        getDB()->begin_transaction();
        $r = getDB()->prepare("DELETE likes from likes join emotes e on e.id = likes.emoteID where e.id = ?;");
        $r->bind_param('i', $this->id);
        $r->execute();
        $r = getDB()->prepare('DELETE emotes from emotes where id = ?');
        $r->bind_param('i', $this->id);
        $r->execute();
        getDB()->commit();
    }

}