<?php

    namespace Runtime;

    if(!defined('RUNTIME_TEST'))
        die('Unauthorized access !');

    require_once CONFIG_APP_CONFIG.DS."db_config.php";


    /**
     * Class database
     * @version 1.10.130215
     * @author: Tomas Hujer
     * @licence Copyright (C) 2010 - 2014 Tomas Hujer. All rights reserved.
     *
     * History
     *
     * 1.09.120515  - přidána podpora uložených procedur
     *
     * 1.10.130215  - doplněna metoda call_stored_proc podporující multiresult
     *
     */
    class database extends App\Config\db_config
    {
        public  $connection     = null;
        public  $connected      = false;
        public  $query_string   = null;
        protected static $instance = null;

        function __construct()
        {
        }

        /**
         * Return or create singleton instance
         */
        public static function get_instance() {

            if(self::$instance instanceof self)
                return self::$instance;
            else {
                self::$instance = new self();
                return self::$instance;
            }
        }


        /**
         * Generate connection string
         * @return string
         */
        protected function get_connection_string() {

            $c = $this->get_config();
            $str = "mysql://{$c->db_user}:{$c->db_pass}@{$c->db_host}/{$c->db_name};charset=utf8";
            return $str;
        }


        /**
         * Call stored procedure with params and retrieve multiresult
         * @param $name array stored procedure name
         * @param $a_params array stored procedure parameters
         * @return array of result objects
         */
        public function call_stored_proc($name, $a_params) {

            if(!defined('LN'))
                define('LN', "\n");

            $c = $this->get_config();

            try {
                $o_db = new \mysqli($c->db_host, $c->db_user, $c->db_pass, $c->db_name);

                $o_db->query('SET CHARACTER SET utf8');
                $o_db->set_charset('utf8');
                $o_db->query("SET NAMES `utf8`");
            } catch(\Exception $e) {
                echo "Can't connect to database ".$c->db_name.' !';
                return false;
            }

            $s_params = '';

            $i = 0;
            foreach($a_params as $param) {
                if($i++)
                    $s_params .= ', ';
                $s_params .= "'$param'";
            }

            $query = " CALL $name($s_params);".LN;

            $a_output = array();
            $a_result = array();

            if(mysqli_multi_query($o_db, $query)) {
                do {
                    $result = mysqli_store_result($o_db);

                    /*if(!empty($result)) {*/
                        if(is_object($result) || is_array($result))
                        {
                            while ($row = $result->fetch_object())
                                $a_result[] = $row;
                        }
                        else
                            $a_result = $result;

                        @$a_output[] = array(
                            'num_rows' => $result->num_rows,
                            'current_field' => $result->current_field,
                            'field_count' => $result->field_count,
                            'result' => $a_result,
                            'error' => $o_db->error,
                            'query' => $query
                        );

                        unset($a_result);

                        if(is_object($result))
                                $result->free_result();
                        
                    //}
                }
                while(mysqli_more_results($o_db) && mysqli_next_result($o_db)); 
            } else {
                @$a_output[] = array(
                    'error' => $o_db->error,
                    'query' => $query
                );
            }

            $o_db->close();

            return $a_output;

        }    

    }
