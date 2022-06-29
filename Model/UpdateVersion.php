<?php
    class UpdateVersion extends UserController
    {
        public function updateVersion()
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
                    $version = isset($data["vers"]) ? trim($data["vers"]) : '';
                    $file_name = isset($data["file_name"]) ? trim($data["file_name"]) : '';

                    $userModel = new UserModelUpdateVersion();

                    if($version && $file_name && is_numeric($version))
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
            $query = "SELECT `file_num` FROM `file_locations` WHERE `file_name` = ?";

            $result = $this->execute($query, 's', array($name));

            if(!$result)
                return array(
                    'Header' => 'HTTP/1.1 400 Bad Request',
                    'Index' => 'Error',
                    'Response' => 'File Does Not Exist'
                );

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