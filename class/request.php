<?php

    namespace Runtime;

    /*  +----------------------------------------------------------+
     *  |   Class Request                                          |
     *  |   Version 1.03.130422                                    |
     *  |   Author: Tomas Hujer                                    |
     *  +----------------------------------------------------------+
     *
     * 1.03.130422
     * - přidáno automatické parsování php://input pokud je prázdná
     *   požadovaná proměnná
     */

    /**
     * Remove danger tags from string
     * @param $value
     * @param $key
     * @param int $length
     * @return mixed
     */
    function clean_item($value, $key, $length = 0)
    {
        // Remove multiple line feed
        $value = preg_replace('~(\r\n)+~','\r\n', $value);

        // Remove line end if string has got it
        $value = preg_replace('~(\r\n)$~', "", $value);

        // Remove <script tag
        $value = preg_replace('~(<script)~', "", $value);
        $value = preg_replace('~(</script>)~', "", $value);

        $value = preg_replace('~(<\?)~', "", $value);
        $value = preg_replace('~(\?>)~', "", $value);

        $value = preg_replace('~(<php)~', "", $value);
        $value = preg_replace('~(php>)~', "", $value);

        // Remove <embed tag
        $value = preg_replace('~(<embed)~', "", $value);

        // Remove <link tag
        $value = preg_replace('~(<link)~', "", $value);


        // Limit input length
        if($length)
            $value = substr($value, 0, $length);

        return $value;
    }


    /**
     * Input stripping
     *
     * @author Tomas Hujer
     */
    class request
    {
        public function __construct ()
        {
        }

        /**
         * Universal global input array reader
         * @param $name
         * @param null $source Source array
         * @param null $default_value Default value
         * @param int $length Max length
         * @return array|mixed|null
         */
        public static function get_var($name, $source = null, $default_value = null, $length = 0)
        {
            if($source === 'METHOD')
            {
                // Get source from server variable
                $source = strtoupper( $_SERVER['REQUEST_METHOD'] );
            }

            // Get the input hash
            switch ($source)
            {
                case 'GET':
                    $input = &$_GET;
                    break;
                case 'POST':
                    $input = &$_POST;
                    break;
                case 'REQUEST':
                    $input = &$_REQUEST;
                    break;
                case 'FILES':
                    $input = &$_FILES;
                    break;
                case 'COOKIE':
                    $input = &$_COOKIE;
                    break;
                case 'ENV':
                    $input = &$_ENV;
                    break;
                case 'SERVER':
                    $input = &$_SERVER;
                    break;
                case 'SESSION':
                    $input = &$_SESSION;
                    break;
                default:
                    $input = &$_REQUEST;
                    break;
            }

            if(!count($input)) {
                $input = file_get_contents ('php://input');
                parse_str( html_entity_decode($input), $input);
            }

            $source = @$input[$name];

            if(is_array($source))
            {
                array_walk_recursive($source, 'clean_item');
                return($source);
            }
            else
            {
                $value = clean_item($source, 0,$length);
            }

            if((!empty($default_value)) &&
               (empty($value)))
                $value = $default_value;

            return($value);
        }


        /**
         * Set variable in global array
         * @param $name Name of value
         * @param $value Value
         * @param null $source Global array
         */
        public static function set_var($name, $value, $source = null)
        {
            if($source === 'METHOD')
            {
                // Get source from server variable
                $source = strtoupper($_SERVER['REQUEST_METHOD']);
            }

            // Get the input hash
            switch ($source)
            {
                case 'GET':
                    $variable = &$_GET;
                    break;
                case 'POST':
                    $variable = &$_POST;
                    break;
                case 'FILES':
                    $variable = &$_FILES;
                    break;
                case 'COOKIE':
                    $variable = &$_COOKIE;
                    break;
                case 'ENV':
                    $variable = &$_ENV;
                    break;
                case 'SERVER':
                    $variable = &$_SERVER;
                    break;
                case 'SESSION':
                    $variable = &$_SESSION;
                    break;
                default:
                    $variable = &$_REQUEST;
                    break;
            }

            $variable[$name] = $value;
        }


        /**
         * Test global array value for setup
         * @param $name
         * @param null $source
         * @return bool
         */
        public static function is_set($name, $source = null)
        {
            if($source === 'METHOD')
            {
                // Get source from server variable
                $source = strtoupper( $_SERVER['REQUEST_METHOD'] );
            }

            if(!$source)
                $source = 'REQUEST';

            // Get the input hash
            switch ($source)
            {
                case 'GET':
                    $input = &$_GET;
                    break;
                case 'POST':
                    $input = &$_POST;
                    break;
                case 'REQUEST':
                    return(isset($_REQUEST[$name]));
                    break;
                case 'FILES':
                    $input = &$_FILES;
                    break;
                case 'COOKIE':
                    $input = &$_COOKIE;
                    break;
                case 'ENV':
                    $input = &$_ENV;
                    break;
                case 'SERVER':
                    $input = &$_SERVER;
                    break;
                case 'SESSION':
                    $input = &$_SESSION;
                    break;
                default:
                    $input = &$_REQUEST;
                    break;
            }

            return(isset($input[$name]));
        }


        /**
         * Session register - set (array) value of register
         * @param $register Name of register
         * @param array $data_array Array of values to write
         */
        static function set_register($register, $data_array = array())
        {
            $_SESSION['reg_'.$register] = $data_array;
        }


        /**
         * Session register - set value in array
         * @param $register Register name
         * @param $name Value name
         * @param $value Value to write
         */
        static function set_register_var($register, $name, $value)
        {
            $_SESSION['reg_'.$register][$name] = $value;
        }


        /**
         * Session register - add value to array in register
         * @param $register Register name
         * @param $name Value name
         * @param $value Value
         */
        static function add_register_var($register, $name, $value)
        {
            if(!isset($_SESSION['reg_'.$register][$name]))
                $_SESSION['reg_'.$register][$name] = array();

            $_SESSION['reg_'.$register][$name][] = $value;
        }

        /**
         * Session register - get value of register
         * @param $register Register name
         * @param $name Value name
         * @return array|bool
         */
        static function get_register_var($register, $name)
        {
            if(isset($_SESSION['reg_'.$register]))
                $reg = $_SESSION['reg_'.$register][$name];
            else
                return false;

            if(is_array($reg))
                array_walk_recursive($reg, 'clean_item');

            return($reg);
        }


        /**
         * Session register - get whole value of register (array)
         * @param $register Register name
         * @return array
         */
        static function get_register($register)
        {
            $reg = $_SESSION['reg_'.$register];

            if(is_array($reg))
                array_walk_recursive($reg, 'clean_item');

            return($reg);
        }


        /**
         * Session register - Returns keys defined in register
         * @param $register
         * @return array
         */
        static function get_register_keys($register)
        {
            $reg = array_keys($_SESSION['reg_'.$register]);

            if(is_array($reg))
                array_walk_recursive($reg, 'clean_item');

            return($reg);
        }


        /*
         * Nacteni vsech promennych z variables_array do session registru
         */
        /**
         * Store filled values into Session register
         * @param $register Register name
         * @param $variables_array Array of values
         * @param string $method Method type
         */
        static function register_form_data($register, $variables_array, $method = 'POST')
        {
            foreach($variables_array as $var_name)
            {
                // Register only initiated values
                if(self::is_set($var_name, $_REQUEST))
                {
                    $value = request::get_var($var_name, $method);
                    request::set_register_var($register, $var_name,  $value);
                }
            }
        }

        /**
         * Returns true if register exists
         * @param $register
         * @return bool
         */
        static function register_exists($register)
        {
            return isset($_SESSION['reg_'.$register]);
        }

        /**
         * Returns true if register value exists
         * @param $register
         * @param $value
         * @return bool
         */
        static function register_value_exists($register, $value)
        {
            return isset($_SESSION['reg_'.$register][$value]);
        }


        /**
         * Destroy session register variable
         * @param $register
         * @param $name
         */
        static function destroy_register_var($register, $name)
        {
            unset($_SESSION['reg_'.$register][$name]);
        }

        /**
         * Destroy session register
         * @param $register
         */
        static function destroy_register($register)
        {
            unset($_SESSION['reg_'.$register]);
        }


        /**
         * Destroy global array variable
         * @param $name
         * @param null $source
         */
        public static function destroy($name, $source = null)
        {
            if($source === 'METHOD')
            {
                // Get source from server variable
                $source = strtoupper($_SERVER['REQUEST_METHOD']);
            }

            // Get the input hash
            switch ($source)
            {
                case 'GET':
                    $variable = &$_GET;
                    break;
                case 'POST':
                    $variable = &$_POST;
                    break;
                case 'FILES':
                    $variable = &$_FILES;
                    break;
                case 'COOKIE':
                    $variable = &$_COOKIE;
                    break;
                case 'ENV':
                    $variable = &$_ENV;
                    break;
                case 'SERVER':
                    $variable = &$_SERVER;
                    break;
                case 'SESSION':
                    $variable = &$_SESSION;
                    break;
                default:
                    $variable = &$_REQUEST;
                    break;
            }

            unset($variable[$name]);
        }


        /**
         * @param $name
         * @param null $source
         * @param null $default_value
         * @param int $length
         * @return string
         */
        static function get_text($name, $source = null, $default_value = null, $length = 0)
        {
            $value = self::get_var($name, $source, $default_value, $length);
            $value = htmlspecialchars($value);

            return($value);
        }


        /**
         * @return string
         */
        static function get_current_url()
        {
            $protocol = strpos(strtolower(self::get_var('SERVER_PROTOCOL', 'SERVER')),'https') === FALSE ? 'http' : 'https';
            $host     = self::get_var('HTTP_HOST', 'SERVER');
            $script   = self::get_var('SCRIPT_NAME', 'SERVER');
            $params   = self::get_var('QUERY_STRING', 'SERVER');
            $currentUrl = $protocol . '://' . $host . $script . '?' . $params;

            return $currentUrl;
        }


        /**
         * @param $values_array
         * @return string
         */
        static function get_values_add($values_array)
        {
            $protocol = strpos(strtolower(self::get_var('SERVER_PROTOCOL', 'SERVER')),'https') === FALSE ? 'http' : 'https';
            $host     = self::get_var('HTTP_HOST', 'SERVER');
            $script   = self::get_var('SCRIPT_NAME', 'SERVER');

            $params = null;
            $get_array = array_merge($_GET, $values_array);
            $last_key = end(array_keys($get_array));
            foreach($get_array as $key => $value)
            {
                $params .= clean_item($key.'='.$value, 0, 255);

                if($key != $last_key)
                    $params .= '&';
            }

            $url = $protocol.'://'.$host.$script.'?'.$params;
            return($url);
        }


        /**
         * @param $key_to_delete
         * @return string
         */
        static function get_values_del($key_to_delete)
        {
            $protocol = strpos(strtolower(self::get_var('SERVER_PROTOCOL', 'SERVER')),'https') === FALSE ? 'http' : 'https';
            $host     = self::get_var('HTTP_HOST', 'SERVER');
            $script   = self::get_var('SCRIPT_NAME', 'SERVER');

            $params = null;
            $i=0;
            foreach($_GET as $key => $value)
            {
                if($key != $key_to_delete)
                {
                    if($i++)
                        $params .= '&';
                    $params .= clean_item($key.'='.$value, 0, 255);
                }
            }

            $url = $protocol.'://'.$host.$script.'?'.$params;
            return($url);
        }


        /**
         * @param $s_addr
         * @return array
         */
        static public function get_vars_from_addr($s_addr) {
            $a_out = array();
            $a_params = explode('&', $s_addr);

            foreach($a_params as $s_param) {
                $a_var = explode('=', $s_param);
                $a_out[$a_var[0]] = $a_var[1];
            }

            return $a_out;
        }

    }

