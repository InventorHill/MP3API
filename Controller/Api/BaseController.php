<?php
    class BaseController
    {
        public function __call($name, $arguments)
        {
            $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
        }

        protected function getUriSegments()
        {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = explode('/', $uri);

            return $uri;
        }

        protected function getQueryStringParams()
        {
            return parse_str($_SERVER['QUERY_STRING'], $query);
        }

        protected function sendOutput($data, $httpHeaders = array(), $download = '')
        {
            header_remove('Set-Cookie');

            if(is_array($httpHeaders) && count($httpHeaders)) 
            {
                foreach ($httpHeaders as $httpHeader)
                    header($httpHeader);
            }

            if($download)
            {
                if (ob_get_level())
                    ob_end_clean();
                
                readfile($download);

                $inStream = fopen($download, 'rb');
                $outStream = fopen('php://output', 'wb');

                stream_copy_to_stream($inStream, $outStream);
                
                fclose($inStream);
                fclose($outStream);
            }
            else
                echo $data;

            exit;
        }
    }
?>