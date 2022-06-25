<?php
    class CheckUpdate extends UserController
    {
        public function checkUpdate()
        {
            $requestMethod = strtoupper($_SERVER["REQUEST_METHOD"]);

            $strHeader = 'HTTP/1.1 200 OK';
            $arrIndex = 'OK';

            if($requestMethod == 'GET')
            {
                try
                {
                    $userModel = new UserModelCheckUpdate();

                    $files = file_get_contents("php://input");

                    if(!$files)
                    {
                        $responseData = "Files Not Supplied";
                        $strHeader = 'HTTP/1.1 400 Bad Request';
                        $arrIndex = 'Error';
                    }
                    else
                    {
                        $files = json_decode($files, true);

                        $responseData = $userModel->checkUpdates($files);

                        if(!$responseData && !is_array($responseData))
                        {
                            $responseData = "Could Not Find Updates";
                            $strHeader = 'HTTP/1.1 400 Bad Request';
                            $arrIndex = 'Error';
                        }
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

    class UserModelCheckUpdate extends Database
    {
        public function checkUpdates($files = [])
        {
            $names = $files["file_names"];
            $versions = $files["versions"];

            if(count($names) !== count($versions))
                return false;

            if(count($names) == 0)
            {
                $query = "SELECT * FROM `file_locations`";
                return $this->execute($query);
            }

            $queries = [];
            $queries[] = "CREATE TEMPORARY TABLE `tmp`(file_name varchar(512), version double);";
            $queries[] = "INSERT INTO `tmp` VALUES ";

            $params = '';

            $param_data = [];
            
            for($i = 0; $i < count($names); $i++)
            {
                $queries[1] .= "(?, ?), ";
                $params .= 'ss';
                $param_data[] = $names[$i];
                $param_data[] = $versions[$i];
            }

            $queries[1] = substr($queries[1], 0, -2) . ";";

            $queries[] = "CREATE TEMPORARY TABLE `new_table`
                (file_num INT(11), file_name varchar(512), version double, server_dir varchar(512), pi_dir varchar(512));";

            $queries[] = "INSERT INTO `new_table` (file_num, file_name, version, server_dir, pi_dir)
                SELECT locs.* FROM `file_locations` locs INNER JOIN `tmp` t ON locs.file_name = t.file_name AND locs.version > t.version;";

            $queries[] = "CREATE TEMPORARY TABLE `new_table1`
                (file_num INT(11), file_name varchar(512), version double, server_dir varchar(512), pi_dir varchar(512));";

            $queries[] = "INSERT INTO `new_table1` (file_num, file_name, version, server_dir, pi_dir)
                SELECT * FROM `new_table`;";

            $queries[] = "INSERT INTO `new_table1` (file_num, file_name, version, server_dir, pi_dir)
                SELECT * FROM `file_locations` WHERE `file_name` NOT IN (SELECT `file_name` FROM `tmp`);";

            $queries[] = "SELECT * FROM `new_table1`;";

            $this->execute($queries[0]);

            $this->execute($queries[1], $params, $param_data);

            for($i = 2; $i < count($queries) - 1; $i++)
                $this->execute($queries[$i]);

            $filesToSend = $this->execute(end($queries));

            $queries = [];
            $queries[] = "CREATE TEMPORARY TABLE `tmp_up`(file_name varchar(512), version double);";
            $queries[] = "INSERT INTO `tmp_up` VALUES ";

            $params = '';

            $param_data = [];
            
            for($i = 0; $i < count($names); $i++)
            {
                $queries[1] .= "(?, ?), ";
                $params .= 'ss';
                $param_data[] = $names[$i];
                $param_data[] = $versions[$i];
            }

            $queries[1] = substr($queries[1], 0, -2) . ";";

            $queries[] = "CREATE TEMPORARY TABLE `new_table_up` 
                (file_name varchar(512), version double, updt BOOLEAN DEFAULT TRUE);";

            $queries[] = "INSERT INTO `new_table_up` (file_name, version)
                SELECT t.* FROM `tmp_up` t INNER JOIN `file_locations` locs ON t.file_name = locs.file_name AND t.version > locs.version;";

            $queries[] = "CREATE TEMPORARY TABLE `new_table1_up`
                (file_name varchar(512), version double, updt BOOLEAN DEFAULT FALSE);";

            $queries[] = "INSERT INTO `new_table1_up` (file_name, version, updt)
                SELECT * FROM `new_table_up`;";

            $queries[] = "INSERT INTO `new_table1_up` (file_name, version)
                SELECT * FROM `tmp_up` WHERE `file_name` NOT IN (SELECT `file_name` FROM `file_locations`);";

            $queries[] = "SELECT * FROM `new_table1_up`;";

            $this->execute($queries[0]);
            $this->execute($queries[1], $params, $param_data);

            for($i = 2; $i < count($queries) - 1; $i++)
                $this->execute($queries[$i]);

            $filesToReceive = $this->execute(end($queries));

            return array('Download' => $filesToSend, 'Upload' => $filesToReceive);
        }
    }
?>