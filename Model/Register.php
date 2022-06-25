<?php
    class Register extends UserController
    {
        public function register()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $name = isset($_GET['name']) ? trim($_GET['name']) : '';
            $pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if(strtoupper($requestMethod) == 'POST')
            {
                try
                {
                    $userModel = new UserModelRegister();

                    if(!$name || !$pass || strlen($pass) < 8)
                    {
                        $responseData = "Invalid Arguments";
                        $strHeader = 'HTTP/1.1 400 Bad Request';
                        $arrIndex = 'Error';
                    }
                    else
                    {
                        $response_arr = $userModel->register($name, $pass);

                        $responseData = $response_arr['Response'];
                        $strHeader = $response_arr['Header'];
                        $arrIndex = $response_arr['Index'];
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

            return array(json_encode(array($arrIndex => $responseData)), array('Content-Type: application/json', $strHeader));
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

            $query = "SELECT `device_num` FROM `registered_devices` 
                WHERE `device_name` = ? UNION SELECT `device_num` FROM `device_requests` WHERE `device_name` = ?";

            $result = $this->execute($query, 'ss', array($name, $name));

            try
            {
                if (is_array($result) && count($result) == 0)
                {
                    $query = "INSERT INTO `device_requests` (`device_name`, `device_pass`, `timestamp`) VALUES (?, ?, ?)";
                    $result = $this->execute($query, 'sss', array($name, $hash_pass, $timestamp));

                    if($result)
                        return array(
                            'Header' => 'HTTP/1.1 200 OK',
                            'Index' => 'OK',
                            'Response' => 'Device Request Added Successfully'
                        );
                    else
                        return array(
                            'Header' => 'HTTP/1.1 500 Internal Server Error',
                            'Index' => 'Error',
                            'Response' => 'Could Not Register Device'
                        );
                }
                else
                    return array(
                        'Header' => 'HTTP/1.1 400 Bad Request',
                        'Index' => 'Error',
                        'Response' => 'Device Name Already Exists'
                    );
            }
            catch (Exception $e)
            {
                return array(
                    'Header' => 'HTTP/1.1 400 Bad Request',
                    'Index' => 'Error',
                    'Response' => 'Device Name Already Exists'
                );
            }
        }
    }
?>