<?php

    namespace Runtime;

    use Runtime\App\Controller\app;

    if(!defined('RUNTIME_TEST'))
        die('Unauthorized access !');

    /**
     * Class Runtime
     * @version 1.04.140918
     * @author Tomas Hujer
     */
    class core
    {
        /**
         * Constructor
         * Include required libraries
         * Add javascript sources
         */
        function __construct()
        {
            session_start();
        }


        /**
         * Process client request
         */
        public function process()
        {
            $a_params = array();

            // Set default values
            $a_params['app_name']     = CONFIG_APP_NAME;
            $a_params['title']        = CONFIG_DEFAULT_TITLE;
            $a_params['keywords']     = CONFIG_DEFAULT_KEYWORDS;
            $a_params['descriptions'] = CONFIG_DEFAULT_DESCRIPTION;
            $a_params['robots']       = CONFIG_DEFAULT_ROBOTS;
            $a_params['content_type'] = CONFIG_DEFAULT_CONTENT_TYPE;
            $a_params['web_author']   = 'Tomáš Hujer';
            $a_params['favicon']      = CONFIG_DEFAULT_FAVICON;

            $a_params['layout']       = CONFIG_LAYOUT;
            $a_params['dir']          = CONFIG_LAYOUT_DIR . CONFIG_LAYOUT . DS;

            $a_params['language']     = 'cs';

            $uri_params = (defined('STDIN')) ? http::get_cli_params() : http::get_uri_params();

            if(!is_array($uri_params)) {
                // Parse values from raw web address
                $uri_params = request::get_vars_from_addr($uri_params);
            }

            // Get Ajax state
            $uri_params['b_ajax'] = request::get_var('b_ajax');

            // Combine arrays (items from first array take precedence)
            $uri_params = $a_params + $uri_params;

            $app = new app();
            $a_params['content'] = $app->process($uri_params);

            $o_layout = layout::get_instance();
            echo $o_layout->render($a_params);
        }


        /**
         * At the End
         */
        public function done() {
        }

    }
