<?php

$uri = $_SERVER['REQUEST_URI'];
var_dump($_REQUEST);
var_dump($_GET);
var_dump($_POST);
echo <<<END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emotes Open Collection</title>
    <meta http-equiv="cache-control" content="no-cache, must-revalidate">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.png" />
</head>

<body>
<h1>EOC</h1>
<br><br>
$uri
</body>

</html>
END;
