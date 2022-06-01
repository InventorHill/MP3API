<?php
    define("DIRSEP", DIRECTORY_SEPARATOR);
    define("PROJECT_ROOT_PATH", __DIR__ . sprintf("%s..%s", DIRSEP, DIRSEP));

    define("CLASS_NAMES", array(
        'register' => 'Register',
        'checkUpdate' => 'CheckUpdate',
        'add' => 'AddFile',
        'update' => 'UpdateVersion',
        'download' => 'DownloadFile',
        'upload' => 'UploadFile'));

    require_once PROJECT_ROOT_PATH . sprintf("%sinc%sconfig.php", DIRSEP, DIRSEP);
    
    require_once PROJECT_ROOT_PATH . sprintf("%sController%sApi%sBaseController.php", DIRSEP, DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sController%sApi%sUserController.php", DIRSEP, DIRSEP, DIRSEP);

    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sDatabase.php", DIRSEP, DIRSEP);

    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sAddFile.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sCheckUpdate.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sDownloadFile.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sRegister.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sUpdateVersion.php", DIRSEP, DIRSEP);
    require_once PROJECT_ROOT_PATH . sprintf("%sModel%sUploadFile.php", DIRSEP, DIRSEP);
?>