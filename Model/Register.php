<?php
    class Register extends UserController
    {
        public function register()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $name = $_GET['name'];
            $pass = $_POST['pass'];

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if(strtoupper($requestMethod) == 'POST')
            {
                try
                {
                    $userModel = new UserModelRegister();

                    if(!$name || !$pass)
                    {
                        $responseData = "Missing Arguments";
                        $strHeader = 'HTTP/1.1 400 Bad Request';
                        $arrIndex = 'Error';
                    }
                    else
                    {
                        $responseData = $userModel->register($name, $pass);

                        if(!$responseData)
                        {
                            $responseData = "Could Not Register Device";
                            $strHeader = 'HTTP/1.1 400 Bad Request';
                            $arrIndex = 'Error';
                        }
                        else
                            $responseData = "Device Request Added Successfully";
                    }
                }
                catch (Error $e)
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

    class UserModelRegister extends Database
    {
        public function register($name = '', $pass = '')
        {
            $datum = new DateTime();
            $timestamp = $datum->format("Y-m-d H:i:s");

            $salt = hash('md5', $timestamp);
            $hash_pass = hash('sha512', $pass . $salt);

            $query = "INSERT INTO `device_requests` (`device_name`, `device_pass`, `timestamp`) VALUES (?, ?, ?)";

            return $this->execute($query, 'sss', array($name, $hash_pass, $timestamp));
        }
    }
?>