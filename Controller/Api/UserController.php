<?php
    class UserController extends BaseController 
    {
        public function performAction($apiName = '')
        {
            if($apiName && isset(CLASS_NAMES[$apiName]))
            {
                $headers = apache_request_headers();
                if($apiName != 'login')
                {
                    if(!isset($headers['Authorization']))
                        $this->sendOutput('Service Unavailable', array('HTTP/1.1 403 Forbidden'));

                    $header = $headers['Authorization'];

                    if(substr($header, 0, 7) != 'Bearer ')
                        $this->sendOutput('Service Unavailable', array('HTTP/1.1 403 Forbidden'));

                    $header = explode(".", substr($header, 7));
                    
                    $rsa = new Rsa();

                    if(!$rsa->rsa256_decrypt($header[0] . "." . $header[1], PRIVATE_D, PUBLIC_N, $header[3]))
                        $this->sendOutput('Service Unavailable', array('HTTP/1.1 403 Forbidden'));
                }

                $className = CLASS_NAMES[$apiName];

                $class = new $className();
                $this->arrayToOutput($class->{lcfirst($className)}());
            }
            else
                $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
        }
        
        public function onlineAction()
        {
            $this->sendOutput(json_encode(array('OK' => 'Server Online')), array('Content-Type: application/json', 'HTTP/1.1 200 OK'));
        }
    }
?>