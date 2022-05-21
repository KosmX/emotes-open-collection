<?php declare(strict_types=1);

use emotes\Emote;
use routing\Routes;

function debugger(): Routes
{
    var_dump($_REQUEST);
    var_dump($_GET);
    var_dump($_POST);
    var_dump($_SERVER['HTTP_HOST']);
    var_dump($_SESSION);
    var_dump($_FILES);


    /** @var Emote $emote */
    $emote = Emote::get(1);

    $form = $emote->getEdit("/debug");
    echo $form->build();

    var_dump(ceil(6/2));

    /*
    $a = new \java\EmoteDaemonClient();
    var_dump(unpack('c', '*1234'));
    #$a->addData(file_get_contents('emotecraft_export/Waving.emotecraft'), 1);
    $a->addData(file_get_contents('json_export/bee5.json'), 2);
    $a->addData(file_get_contents('json_export/bee5.png'), 3);
    $json = array(
        'name' => 'TestModified stuff',
        'description' => 'Not Bee (it is actually)',
        'author' => 'Not KosmX',
        'uuid' => '0dc7cfe3-abfb-47b9-a0e5-6b51d8e45fdf'
    );
    $a->addData(json_encode($json), 8);
    $result = $a->exchange(array(8, 1));
    #file_put_contents('test.emotecraft', $result[1]['data']);
    var_dump($result);
    */


    return Routes::SELF_SERVED;
}
