<?php
    class AddFile extends UserController
    {
        public function addFile()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $data = file_get_contents("php://input");

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if(!$data)
            {
                $responseData = "Missing Data";
                $strHeader = 'HTTP/1.1 400 Bad Request';
                $arrIndex = 'Error';
            }
            else if(strtoupper($requestMethod) == 'GET')
            {
                try
                {
                    $data = json_decode($data, true);

                    $version = isset($data["vers"]) ? trim($data['vers']) : '';

                    $dir = isset($data["dir"]) ? trim($data["dir"]) : '';
                    $file = isset($data["file"]) ? trim($data["file"]) : '';
                    $pi_file = isset($data["pi_file"]) ? trim($data["pi_file"]) : '';

                    $userModel = new UserModelAddFile();

                    if ($version && is_numeric($version))
                    {
                        if ($dir)
                        {
                            $response_arr = $userModel->addFiles($version, $dir);

                            $responseData = $response_arr['Response'];
                            $strHeader = $response_arr['Header'];
                            $arrIndex = $response_arr['Index'];
                        }
                        else
                        {
                            if($file && $pi_file)
                            {
                                $response_arr = $userModel->addFile($version, $file, $pi_file);

                                $responseData = $response_arr['Response'];
                                $strHeader = $response_arr['Header'];
                                $arrIndex = $response_arr['Index'];
                            }
                            else
                            {
                                $responseData = "Missing Data";
                                $strHeader = 'HTTP/1.1 400 Bad Request';
                                $arrIndex = 'Error';
                            }
                        }
                    }
                    else
                    {
                        $responseData = "Invalid Version";
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

            return array(json_encode(array($arrIndex => $responseData)), array('Content-Type: application/json', $strHeader));
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

            $worked = 0;
            $total_count = count($server_files);

            for ($i = 0; $i < count($server_files); $i++)
            {
                $worked += $this->execute($query, 'sdss', array($names[$i], $version, $server_files[$i], $pi_files[$i])) ? 1 : 0;
            }

            $failed = $total_count - $worked;

            if($failed == 0)
                return array(
                    'Header' => 'HTTP/1.1 200 OK',
                    'Index' => 'OK',
                    'Response' => 'Files Added Successfully'
                );
            else
                return array(
                    'Header' => 'HTTP/1.1 500 Internal Server Error',
                    'Index' => 'Error',
                    'Response' => $failed . ' Out Of ' . $total_count . ' Files Unsuccessfully Added'
                );
        }

        public function addFile($version = '', $file = '', $pi_file = '') 
        {
            $server_file = str_replace("!", DIRSEP, $file);
            $pi_file = str_replace("!", "/", $pi_file);
            $name = basename($server_file);

            $query = "SELECT `version` FROM `file_locations` WHERE `file_name` = ?";

            $result = $this->execute($query, 's', array($name));

            if($result)
                return array(
                    'Header' => 'HTTP/1.1 422 Unprocessable Entity',
                    'Index' => 'Error',
                    'Response' => 'File Already Exists'
                );

            $query = "INSERT INTO `file_locations` (`file_name`, `version`, `server_dir`, `pi_dir`) VALUES (?, ?, ?, ?);";

            $result = $this->execute($query, 'sdss', array($name, $version, $server_file, $pi_file));

            if($result)
                return array(
                    'Header' => 'HTTP/1.1 200 OK',
                    'Index' => 'OK',
                    'Response' => 'File Added Successfully'
                );
            else
                return array(
                    'Header' => 'HTTP/1.1 500 Internal Server Error',
                    'Index' => 'Error',
                    'Response' => 'Could Not Add File'
                );
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