<?php declare(strict_types=1);

function debugger(): \routing\Routes
{
    var_dump($_REQUEST);
    var_dump($_GET);
    var_dump($_POST);
    var_dump($_SERVER['HTTP_HOST']);


    $a = array();
    $a['a'] = "b";
    $a['4'] = '2';

    $form = new \elements\SubmitConstantButton(new \elements\LiteralElement("asdf"), $a, "get", "debug.php");
    echo $form->build();

    return \routing\Routes::SELF_SERVED;
}
