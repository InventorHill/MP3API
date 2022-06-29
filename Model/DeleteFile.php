<?php
    class DeleteFile extends UserController
    {
        public function deleteFile()
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
                    $deleted = isset($data["deleted"]) ? trim($data["deleted"]) : '';

                    if(!$version || !$file_name || !$deleted || !is_numeric($version))
                    {
                        $responseData = "Invalid Arguments";
                        $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                        $arrIndex = 'Error';
                    }
                    else
                    {
                        $userModel = new UserModelDeleteFile();
                        $response_arr = $userModel->deleteFile($version, $file_name, strtolower($deleted) == 'true' ? 1 : 0);

                        $responseData = $response_arr['Response'];
                        $strHeader = $response_arr['Header'];
                        $arrIndex = $response_arr['Index'];
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

    class UserModelDeleteFile extends Database
    {
        public function deleteFile($version = '', $name = '', $deleted = '') 
        {
            $query = "SELECT `file_num` FROM `file_locations` WHERE `file_name` = ?";

            $result = $this->execute($query, 's', array($name));

            if(!$result)
                return array(
                    'Header' => 'HTTP/1.1 400 Bad Request',
                    'Index' => 'Error',
                    'Response' => 'File Does Not Exist'
                );

            $query = "UPDATE `file_locations` SET `deleted` = ? WHERE `file_locations`.`file_name` = ? AND `file_locations`.`version` = ?";

            $result = $this->execute($query, 'isd', array($deleted, $name, $version));

            if($result)
                return array(
                    'Header' => 'HTTP/1.1 200 OK',
                    'Index' => 'OK',
                    'Response' => $deleted === 1 ? 'File Deleted Successfully' : 'File Restored Successfully'
                );
            else
                return array(
                    'Header' => 'HTTP/1.1 500 Internal Server Error',
                    'Index' => 'Error',
                    'Response' => $deleted === 1 ? 'Could Not Delete File' : 'Could Not Restore File'
            );
        }
    }
?>