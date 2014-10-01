<?php

    namespace Runtime;

    /*  +----------------------------------------------------------+
     *  | Class HTTP                                               |
     *  | Version 1.03.130620                                      |
     *  +----------------------------------------------------------+
     *
     *  1.02.130110
     *      - v metodě get uri params doplněno načítání všech parametrů do pole a_item
     *
     *  1.03.130620
     *      - doplněna metoda get_cli_params
     */

    /**
     * HTTP tools
     *
     * @author Tomas Hujer, inspired by Zend framework
     */
    class http {

        var $_requestUri = null;
        var $_subDir = null;

        /**
         * @return array|mixed|null
         */
        public static function getHttpHost() {
            return request::get_var('HTTP_HOST', 'SERVER');
        }

        /**
         * @return array|mixed|null
         */
        public static function getServerIPv4() {
            return request::get_var('SERVER_ADDR', 'SERVER');
        }


        /**
         * @return string
         */
        public static function getSubDir() {

            $subdir = '';

            $scriptName = explode('/', $_SERVER['SCRIPT_NAME']);

            if(count($scriptName) > 1)
                $subdir = $scriptName[1];

            return $subdir;
        }


        /**
         * Set the REQUEST_URI on which the instance operates
         *
         * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI'],
         * $_SERVER['HTTP_X_REWRITE_URL'], or $_SERVER['ORIG_PATH_INFO'] + $_SERVER['QUERY_STRING'].
         *
         * @param string $requestUri
         * @return request uri
         */
        public function set_request_uri($requestUri = null) {

            if ($requestUri === null)
            {
                if (isset($_SERVER['HTTP_X_REWRITE_URL']))
                { // check this first so IIS will catch
                    $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
                }
                elseif
                (
                    // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
                    isset($_SERVER['IIS_WasUrlRewritten'])
                        && $_SERVER['IIS_WasUrlRewritten'] == '1'
                        && isset($_SERVER['UNENCODED_URL'])
                        && $_SERVER['UNENCODED_URL'] != ''
                    ) {
                        $requestUri = $_SERVER['UNENCODED_URL'];
                    }
                    elseif(isset($_SERVER['REQUEST_URI']))
                    {
                        $requestUri = $_SERVER['REQUEST_URI'];

                        // Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
                        $schemeAndHttpHost = 'http://' . self::getHttpHost();

                        if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                            $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
                        }

                    }
                    elseif(isset($_SERVER['ORIG_PATH_INFO']))
                    { // IIS 5.0, PHP as CGI
                            $requestUri = $_SERVER['ORIG_PATH_INFO'];

                            if (!empty($_SERVER['QUERY_STRING'])) {
                                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
                            }
                    }
                    else
                    {
                        return $this->_requestUri;
                    }

            }
            elseif(!is_string($requestUri))
            {
                return $this->_requestUri;
            }
            else
            {
                // Set GET items, if available
                if (false !== ($pos = strpos($requestUri, '?')))
                {
                    $query = substr($requestUri, $pos + 1);
                    parse_str($query, $vars);
                    $this->setQuery($vars);
                }
            }

            $this->_requestUri = $requestUri;
            return $this->_requestUri;
        }


        /**
         * @return string
         */
        private static function getWordDelimiter() {
            return CONFIG_WORD_DELIMITER;
        }

        /**
         * Returns the REQUEST_URI taking into account
         * platform differences between Apache and IIS
         *
         * @return string
         */
        public function get_request_uri() {
            if (empty($this->_requestUri)) {
                $this->set_request_uri();
            }

            return $this->_requestUri;
        }


        /**
        * Formats a string into an action name.  This is used to take a raw
        * action name, such as one that would be stored inside a Zend_Controller_Request_Abstract
        * object, and reformat into a proper method name that would be found
        * inside a class extending Action.
        *
        * @param string $unformatted
        * @return string
        */
        public static function formatActionName($unformatted)
        {
            $formatted = self::_formatName($unformatted, true);
            return strtolower(substr($formatted, 0, 1)) . substr($formatted, 1);
        }


        /**
        * Formats a string from a URI into a PHP-friendly name.
        *
        * By default, replaces words separated by the word separator character(s)
        * with camelCaps. If $isAction is false, it also preserves replaces words
        * separated by the path separation character with an underscore, making
        * the following word Title cased. All non-alphanumeric characters are
        * removed.
        *
        * @param string $unformatted
        * @param boolean $isAction Defaults to false
        * @return string
        */
        protected static function _formatName($unformatted, $isAction = false)
        {
            // preserve directories
            if (!$isAction) {
                $segments = explode(DS, $unformatted);
            } else {
                $segments = (array) $unformatted;
            }

            foreach ($segments as $key => $segment) {
                $segment        = str_replace(self::getWordDelimiter(), ' ', strtolower($segment));
                $segment        = preg_replace('/[^a-z0-9 ]/', '', $segment);
                $segments[$key] = str_replace(' ', '', ucwords($segment));
            }

            return implode('_', $segments);
        }


        /**
         * Detect URI parameter
         * - automatically remove subdir and symbols
         * - if SEO syntax used, then convert it to controller, action and parameter
         * @return array
         */
        public static function get_uri_params() {

            $o_http = new http();
            $uri = $o_http->get_request_uri();

            $subdir = http::getSubDir();

            $request = $uri;

            if (strpos($uri, '/'.$subdir.'/') === 0)
                $request = substr($uri, strlen('/'.$subdir));

            if(strpos($request, '/') === 0)
                $request = substr($request, 1);

            if(strpos($request, '?') !== false)
            {
                $request = explode('?', $request);
                $request = $request[0];
            }

            $out = array();

            if(strpos($request, '/') !== false)
            {
                $request = explode('/', $request);

                $out['controller'] = $request[0];
                $out['action'] = self::formatActionName($request[1]);

                for($nl_i = 2; ($nl_i < 10 && (!empty($request[$nl_i]))); $nl_i++) {
                    $out['a_item'][] = $request[$nl_i];
                }
            }
            return $out;
        }


        /**
         * Detect CLI parameter
         * 1. web domain
         * 2. controller
         * 3. action
         * 4. custom parameter
         * @return mixed
         */
        public static function get_cli_params() {
            global $argv;
            global $argc;

            $a_result['domain'] = $argv[1];
            $a_result['controller'] = $argv[2];
            $a_result['action'] = self::formatActionName($argv[3]);
            $a_result['b_ajax'] = 1;

            if($argc >= 4) {
                for($nl_i = 4; ($nl_i < $argc && (!empty($argv[$nl_i]))); $nl_i++) {
                    $a_result['a_item'][] = $argv[$nl_i];
                }
            }

            return $a_result;
        }

    }
