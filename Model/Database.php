<?php
    class Database
    {
        protected $mysql = null;

        public function __construct() 
        {
            try 
            {
                $this->mysql = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DB);

                if(mysqli_connect_errno())
                    throw new Exception("Could not connect to database");
            }
            catch (Exception $e)
            {
                throw new Exception($e->getMessage());
            }
        }

        public function execute($query = "", $bind = '', $params = [])
        {
            try
            {
                $statement = $this->mysql->prepare($query);

                if($statement === false)
                {
                    echo $statement->error;
                    throw new Exception("Unable to perform query: " . $query);
                }

                if($params && $bind)
                {
                    if(is_array($params))
                        $statement->bind_param($bind, ...$params);
                    else
                        $statement->bind_param($bind, $params);
                }

                $statement->execute();
                $result = true;

                if($statement === false)
                    $result = false;
                else
                {
                    try
                    {
                        $result = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
                    }
                    catch(Error $e)
                    {
                        $result = true;
                    }
                }

                $statement->close();

                return $result;
            }
            catch(Exception $e)
            {
                throw new Exception($e->getMessage());
            }

            return false;
        }
    }
?>