<?php declare(strict_types=1);

namespace emotes;

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
        $this->author = $data['author'];
        $this->visibility = $data['visibility'];
        $this->published = $data['published'] != 0;
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
            return new Emote($result->fetch_array());
        }
        return null;
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

}