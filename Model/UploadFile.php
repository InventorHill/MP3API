<?php
    class UploadFile extends UserController
    {
        public function uploadFile()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $submit = isset($_POST["submit"]) ? trim($_POST["submit"]) : '';
            $vers = isset($_GET["vers"]) ? trim($_GET["vers"]) : '';
            $pi_name = isset($_GET["pi_name"]) ? trim($_GET["pi_name"]) : '';
            $update = isset($_GET["update"]) ? trim($_GET["update"]) : '';

            $strHeader = 'HTTP/1.1 200 OK';
            $name = '';
            $content_type = '';
            $arrIndex = 'OK';

            if (!str_contains($pi_name, '!'))
            {
                $responseData = 'Invalid File Path';
                $strHeader = 'HTTP/1.1 400 Bad Request';
                $arrIndex = 'Error';
            }
            else if (strpos($pi_name, '!', -1))
            {
                $responseData = 'Invalid File Name';
                $strHeader = 'HTTP/1.1 400 Bad Request';
                $arrIndex = 'Error';
            }
            else if (!floatval($vers))
            {
                $responseData = "Invalid Version";
                $strHeader = 'HTTP/1.1 400 Bad Request';
                $arrIndex = 'Error';
            }
            else if (strtoupper($requestMethod) == 'POST')
            {
                try
                {
                    if($submit && $vers && $pi_name && $update)
                    {
                        try
                        {
                            $server_loc = str_replace("!", DIRSEP, "C:!pi" . $pi_name);
                            $pi_loc = str_replace("!", "/", $pi_name);
                            $name = basename($server_loc);

                            if(basename($server_loc) != basename($_FILES["fileToUpload"]["name"]))
                            {
                                $responseData = 'Missing File';
                                $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                                $arrIndex = 'Error';
                            }
                            else if(file_exists($server_loc) && strtolower($update) != 'true')
                            {
                                $responseData = 'File Already Exists';
                                $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                                $arrIndex = 'Error';
                            }
                            else if ($_FILES["fileToUpload"]["size"] > 500000000)
                            {
                                $responseData = 'File Too Large';
                                $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                                $arrIndex = 'Error';
                            }
                            else
                            {
                                if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $server_loc))
                                {                                    
                                    if(strtolower($update) == "true")
                                    {
                                        $userModel = new UserModelUpdateVersion();
                                        $response_arr = $userModel->updateVersion($vers, $name);

                                        if(!$response_arr)
                                        {
                                            $responseData = "Could Not Update Version";
                                            $strHeader = 'HTTP/1.1 400 Bad Request';
                                            $arrIndex = 'Error';
                                        }
                                        else
                                        {
                                            $strHeader = $response_arr[1][1];
                                            $response_arr = json_decode($response_arr[0]);
                                            $responseData = $response_arr[0];
                                            $arrIndex = key($response_arr);
                                        }
                                    }
                                    else
                                    {
                                        $userModel = new UserModelAddFile();
                                        $responseData = $userModel->addFile($vers, "C:!pi" . $pi_name, $pi_name);
                                        
                                        if(!$responseData)
                                        {
                                            $responseData = "Could Not Add Files";
                                            $strHeader = 'HTTP/1.1 400 Bad Request';
                                            $arrIndex = 'Error';
                                        }
                                        else
                                            $responseData = 'File Uploaded Successfully';
                                    }
                                }
                                else
                                {
                                    $responseData = 'File Not Uploaded';
                                    $strHeader = 'HTTP/1.1 422 Unprocessable Entity';
                                    $arrIndex = 'Error';
                                }
                            }
                        }
                        catch(Exception $e)
                        {
                            $responseData = $e->getMessage();
                            $arrIndex = 'Error';
                            $strHeader = 'HTTP/1.1 500 Internal Server Error';
                        }
                    }
                    else
                    {
                        $responseData = 'Missing Arguments';
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
?>