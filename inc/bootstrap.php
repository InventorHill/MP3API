<?php
    define("DIRSEP", DIRECTORY_SEPARATOR);
    define("PROJECT_ROOT_PATH", __DIR__ . sprintf("%s..%s", DIRSEP, DIRSEP));
    define("PUBLIC_E", "33629");
    define("PUBLIC_N", "223467822782162459780427993421423477997897203298366032096553818144054548952978290963483480153162472723");
    define("PRIVATE_D", "3979303018507388057881975776676750009646657406062548628966568100069585748780836936003658997795539369");

    define("CLASS_NAMES", array(
        'register' => 'Register',
        'checkUpdate' => 'CheckUpdate',
        'add' => 'AddFile',
        'update' => 'UpdateVersion',
        'download' => 'DownloadFile',
        'upload' => 'UploadFile',
        'delete' => 'DeleteFile',
        'login' => 'Login',
        'rsa' => 'Rsa'));

    require_once PROJECT_ROOT_PATH . sprintf("%sinc%sconfig.php", DIRSEP, DIRSEP);
    
    require_once PROJECT_ROOT_PATH . sprintf("%sController%sApi%sBaseController.php", DIRSEP, DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sController%sApi%sUserController.php", DIRSEP, DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sController%sAuthentication%sJwt.php", DIRSEP, DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sController%sAuthentication%sRsa.php", DIRSEP, DIRSEP, DIRSEP);

    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sDatabase.php", DIRSEP, DIRSEP);

    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sAddFile.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sDeleteFile.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sCheckUpdate.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sDownloadFile.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sRegister.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sLogin.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sUpdateVersion.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sUploadFile.php", DIRSEP, DIRSEP);
?>