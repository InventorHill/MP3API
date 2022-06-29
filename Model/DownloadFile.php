<?php
    class DownloadFile extends UserController
    {
        public function downloadFile()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $file = file_get_contents('php://input');

            $headers = array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error');
            $name = '';
            $response = '';

            if(!$file)
            {
                $strErrorDesc = "Missing File Name";
                $headers[1] = 'HTTP/1.1 422 Unprocessable Entity';
                $response = json_encode(array('Error' => $strErrorDesc));
            }
            else if(strtoupper($requestMethod) == 'GET')
            {
                try
                {
                    $file_name = json_decode($file, true);
                    $file_name = isset($file_name["file_name"]) ? trim($file_name["file_name"]) : '';

                    $database = new Database();

                    if($file_name)
                    {
                        $query = "SELECT * FROM `file_locations` WHERE `file_name` = ?";
                        $responseData = $database->execute($query, 's', basename($file_name));

                        if(!$responseData)
                        {
                            $strErrorDesc = "File Not In Database";
                            $headers[1] = 'HTTP/1.1 400 Bad Request';
                            $response = json_encode(array('Error' => $strErrorDesc));
                        }
                        else
                        {
                            $name = $responseData[0]['server_dir'];
                            $headers = array(
                                "HTTP/1.1 200 OK",
                                "Cache-Control: Public",
                                "Content-Description: File Transfer",
                                "Content-Disposition: attachment; filename=" . basename($name),
                                "Content-Type: " . mime_content_type($name),
                                "Content-Transfer-Encoding: binary",
                                "Content-Length: " . filesize($name));
                        }
                    }
                    else
                    {
                        $strErrorDesc = "Missing File Name";
                        $headers[1] = 'HTTP/1.1 422 Unprocessable Entity';
                        $response = json_encode(array('Error' => $strErrorDesc));
                    }
                }
                catch(Error $e)
                {
                    $strErrorDesc = $e->getMessage();
                    $response = json_encode(array('Error' => $strErrorDesc));
                }
            }
            else
            {
                $strErrorDesc = 'Method Not Supported';
                $headers[1] = 'HTTP/1.1 422 Unprocessable Entity';
                $response = json_encode(array('Error' => $strErrorDesc));
            }

            return array($response, $headers, $name);
        }
    }
?>