<?php
    class Login extends UserController
    {
        public function login()
        {
            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $credentials = file_get_contents('php://input');

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if(!$credentials)
            {
                $responseData = "Invalid Arguments";
                $strHeader = 'HTTP/1.1 400 Bad Request';
                $arrIndex = 'Error';
            }
            else if(strtoupper($requestMethod) == 'POST')
            {
                try
                {
                    $credentials = json_decode($credentials, true);

                    $name = isset($credentials['name']) ? trim($credentials['name']) : '';
                    $pass = isset($credentials['pass']) ? trim($credentials['pass']) : '';

                    $userModel = new UserModelLogin();

                    if(!$name || !$pass || strlen($pass) < 8 || $credentials['pass'] !== $pass)
                    {
                        $responseData = "Invalid Arguments";
                        $strHeader = 'HTTP/1.1 400 Bad Request';
                        $arrIndex = 'Error';
                    }
                    else
                    {
                        $response_arr = $userModel->login($name, $pass, $login == 'true' ? 1 : 0);

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

    class UserModelLogin extends Database
    {
        public function login($name = '', $pass = '')
        {
            $datum = new DateTime();
            $timestamp = $datum->format("Y-m-d H:i:s");

            $query = "SELECT `timestamp` FROM `registered_devices` WHERE `device_name` = ?";

            $result = $this->execute($query, 's', array($name));

            try
            {
                if (is_array($result) && count($result) === 1)
                {
                    $salt = hash('md5', $result[0]['timestamp']);
                    $hash_pass = hash('sha512', $pass . $salt);

                    $query = "SELECT `device_num` FROM `registered_devices` WHERE `device_name` = ? AND `device_pass` = ?";
                    $result = $this->execute($query, 'ss', array($name, $hash_pass));

                    if(is_array($result) && count($result) === 1)
                    {
                        $query = "UPDATE `registered_devices` SET `login_timestamp` = ?, `logged_in` = 1 WHERE `device_name` = ?";

                        $result = $this->execute($query, 'ss', array($timestamp, $name));

                        if($result)
                        {
                            $jwt = new Jwt();

                            return array(
                                'Header' => 'HTTP/1.1 200 OK',
                                'Index' => 'OK',
                                'Response' => $jwt->jwt_generate_token(array("logged_in_as" => $name))
                            );
                        }
                    }
                    else
                    {
                        return array(
                            'Header' => 'HTTP/1.1 422 Unprocessable Entity',
                            'Index' => 'Error',
                            'Response' => 'Incorrect Device Name Or Password'
                        );
                    }
                }
                else
                {
                    return array(
                        'Header' => 'HTTP/1.1 422 Unprocessable Entity',
                        'Index' => 'Error',
                        'Response' => 'Incorrect Device Name Or Password'
                    );
                }
            }
            catch (Exception $e)
            {
                return array(
                    'Header' => 'HTTP/1.1 500 Internal Server Error',
                    'Index' => 'Error',
                    'Response' => 'Login Unsuccessful'
                );
            }
        }
    }
?>