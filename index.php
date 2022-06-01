<?php
    require __DIR__ . sprintf("%sinc%sbootstrap.php", DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);

    if((isset($uri[2]) && $uri[2] != 'pi') || !isset($uri[3]))
    {
        header('HTTP/1.1 404 Not Found');
        exit();
    }

    $objFeedController = new UserController();
    $strMethodName = $uri[3] . 'Action';
    $objFeedController->{$strMethodName}();
?>