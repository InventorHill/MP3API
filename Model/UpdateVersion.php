<?php
    class UpdateVersion extends UserController
    {
        public function updateVersion()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $version = $_GET["vers"];
            $file_name = $_GET["file_name"];

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if(strtoupper($requestMethod) == 'GET')
            {
                try
                {
                    $userModel = new UserModelUpdateVersion();

                    if($version && $file_name)
                    {
                        $responseData = $userModel->updateVersion($version, $file_name);

                        if(!$responseData)
                        {
                            $responseData = "Could Not Update File";
                            $strHeader = 'HTTP/1.1 400 Bad Request';
                            $arrIndex = 'Error';
                        }
                        else
                            $responseData = "Version Updated Successfully";
                    }
                    else
                    {
                        $responseData = "Missing Arguments";
                        $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                        $arrIndex = 'Error';
                    }
                }
                catch(Error $e)
                {
                    $responseData = $e->getMessage();
                    $arrIndex = 'Error';
                    $strHeader = 'HTTP/1.1 500 Internal Server Error';
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

    class UserModelUpdateVersion extends Database
    {
        public function updateVersion($version = '', $name = '') 
        {
            $query = "UPDATE `file_locations` SET `version` = ? WHERE `file_locations`.`file_name` = ?";

            return $this->execute($query, 'ds', array($version, $name));
        }
    }
?>