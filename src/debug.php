<?php declare(strict_types=1);

use pageUtils\UserHelper;

function debugger(): \routing\Routes
{
    var_dump($_REQUEST);
    var_dump($_GET);
    var_dump($_POST);
    var_dump($_SERVER['HTTP_HOST']);
    var_dump($_SESSION);


    $a = array();
    $a['a'] = "b";
    $a['4'] = '2';

    $form = new \elements\SubmitConstantButton(new \elements\LiteralElement("asdf"), $a, "get", "debug.php");
    echo $form->build();


    $str = 'route/to/the/pages';

    $idx = implode('/', array_slice(explode('/', $str), 4 ));

    var_dump($idx);

    $user = new UserHelper('kosmx', 'KosmX', 'kosmx.mc@gmail.com');
    echo $user->getForm('debug.php')->build();

    /*
    $q = getDB()->prepare("INSERT INTO users (email, username, displayName, theCheckbox) value ('kosmx.mc@gmail.com', 'asdf', 'KosmX', true);");
    $q->execute();
    var_dump($q->get_result());
    */

    $str = 'validusername';
    $str2 = 'Invalid|}{UsernME';


    var_dump(preg_match('~^[a-z\\d]+$~', $str));
    //var_dump($m);
    var_dump(preg_match('~^[a-z\\d]+$~', $str2 , $m, PREG_UNMATCHED_AS_NULL));
    var_dump($m);

    return \routing\Routes::SELF_SERVED;
}

function trimUrl(int $depth) {

}
