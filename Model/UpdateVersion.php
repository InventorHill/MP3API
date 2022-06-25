<?php
    class UpdateVersion extends UserController
    {
        public function updateVersion()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $version = isset($_GET["vers"]) ? trim($_GET["vers"]) : '';
            $file_name = isset($_GET["file_name"]) ? trim($_GET["file_name"]) : '';

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if(strtoupper($requestMethod) == 'GET')
            {
                try
                {
                    $userModel = new UserModelUpdateVersion();

                    if($version && $file_name && floatval($version))
                    {
                        $response_arr = $userModel->updateVersion($version, $file_name);

                        $responseData = $response_arr['Response'];
                        $strHeader = $response_arr['Header'];
                        $arrIndex = $response_arr['Index'];
                    }
                    else
                    {
                        $responseData = "Invalid Arguments";
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

            $result = $this->execute($query, 'ds', array($version, $name));

            if($result)
                return array(
                    'Header' => 'HTTP/1.1 200 OK',
                    'Index' => 'OK',
                    'Response' => 'Version Updated Successfully'
                );
            else
                return array(
                    'Header' => 'HTTP/1.1 500 Internal Server Error',
                    'Index' => 'Error',
                    'Response' => 'Could Not Update Version'
            );
        }
    }
?>