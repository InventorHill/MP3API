<?php
    class AddFile extends UserController
    {
        public function addFile()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $version = $_GET["vers"];

            $dir = isset($_GET["dir"]) ? $_GET["dir"] : '';
            $file = isset($_GET["file"]) ? $_GET["file"] : '';
            $pi_file = isset($_GET["pi_file"]) ? $_GET["pi_file"] : '';

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if(strtoupper($requestMethod) == 'GET')
            {
                try
                {
                    $userModel = new UserModelAddFile();

                    if ($version && floatval($version))
                    {
                        if ($dir)
                        {
                            $responseData = $userModel->addFiles($version, $dir);

                            if(!$responseData)
                            {
                                $responseData = "Could Not Add Files";
                                $strHeader = 'HTTP/1.1 400 Bad Request';
                                $arrIndex = 'Error';
                            }
                            else
                                $responseData = "Files Added Successfully";
                        }
                        else
                        {
                            if($file && $pi_file)
                            {
                                $responseData = $userModel->addFile($version, $file, $pi_file);

                                if(!$responseData)
                                {
                                    $responseData = "Could Not Add File";
                                    $strHeader = 'HTTP/1.1 400 Bad Request';
                                    $arrIndex = 'Error';
                                }
                                else
                                    $responseData = "File Added Successfully";
                            }
                            else
                            {
                                $responseData = "Missing File Name";
                                $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                                $arrIndex = 'Error';
                            }
                        }
                    }
                    else
                    {
                        $responseData = "Incorrect Version";
                        $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                        $arrIndex = 'Error';
                    }
                }
                catch(Error $e)
                {
                    $responseData = $e->getMessage();
                    $strHeader = 'HTTP/1.1 500 Internal Server Error';
                    $arrIndex = 'Error';
                }
            }
            else
            {
                $responseData = 'Method Not Supported';
                $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                $arrIndex = 'Error';
            }

            return array(json_encode(array($arrIndex => $responseData)), array('Content-Type: application/json', $strHeader), false);
        }
    }

    class UserModelAddFile extends Database
    {
        public function addFiles($version = '', $dir = '')
        {
            $pi_dir = "/home/pi";
            $server_dir = str_replace("!", DIRSEP, $dir);

            $server_files = $this->listAllFiles($server_dir);
            $pi_files = str_replace($server_dir, $pi_dir, $server_files);
            $pi_files = str_replace(DIRSEP, "/", $pi_files);

            $names = $server_files;

            foreach($names as &$name)
                $name = basename($name);
            
            unset($name);

            $query = "INSERT INTO `file_locations` (`file_name`, `version`, `server_dir`, `pi_dir`) VALUES (?, ?, ?, ?)";

            $worked = False;

            for ($i = 0; $i < count($server_files); $i++)
                $worked |= $this->execute($query, 'sdss', array($names[$i], $version, $server_files[$i], $pi_files[$i]));

            return $worked;
        }

        public function addFile($version = '', $file = '', $pi_file = '') 
        {
            $server_file = str_replace("!", DIRSEP, $file);
            $pi_file = str_replace("!", "/", $pi_file);
            $name = basename($server_file);

            $query = "INSERT INTO `file_locations` (`file_name`, `version`, `server_dir`, `pi_dir`) VALUES (?, ?, ?, ?);";

            return $this->execute($query, 'sdss', array($name, $version, $server_file, $pi_file));
        }

        public function listAllFiles($dir) 
        {
            $array = array_values(array_diff(scandir($dir), array('.', '..')));
            $dirs = [];

            for($i = 0; $i < count($array); $i++)
                $array[$i] = $dir . DIRSEP . $array[$i];

            foreach ($array as $item) 
            {
                if (is_dir($item)) 
                {
                    $dirs[] = $item;
                    $array = array_merge($array, $this->listAllFiles($item));
                }
            }

            $array = array_values(array_diff($array, $dirs));
            return $array;     
        }
    }
?>