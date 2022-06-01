<?php
    class UserController extends BaseController 
    {
        public function performAction($apiName = '')
        {
            if($apiName && isset(CLASS_NAMES[$apiName]))
            {
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