<?php

    namespace Runtime;

    /**
     * Class Layout
     * @author Tomas Hujer
     */
    class layout {

        var $layout_id = CONFIG_LAYOUT;
        var $layout_dir = CONFIG_LAYOUT_DIR;
        var $layout_params = array();

        protected static $instance;

        function __construct() {
        }


        /**
         * Create or return Layout object instance
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
         * Return layout ID
         * @return string
         */
        public function get_layout_id() {
            return $this->layout_id;
        }


        /**
         * Set layout ID
         * @param $s_layout_id
         */
        public function set_layout_id($s_layout_id) {
            $this->layout_id = $s_layout_id;
        }


        /**
         * Set layout parameters
         * @param $a_params
         */
        public function set_layout_params($a_params) {
            foreach($a_params as $s_param_key => $s_param_value)
                $this->layout_params[$s_param_key] = $s_param_value;
        }


        /**
         * Disable layout
         */
        public function disable() {
            $this->layout_id = null;
        }


        /**
         * Render layout
         * @param array $a_params
         * @return string
         */
        public function render($a_params)
        {
            $html = '';
            $b_ajax = request::get_var('b_ajax');

            $this->a_template = array_merge($a_params, $this->layout_params);

            if(!empty($this->layout_id) && (!$b_ajax))
            {
                $this->load_config($this->layout_id);
                $layout_file = "{$this->layout_dir}{$this->layout_id}/{$this->layout_id}.php";

                if(file_exists($layout_file)) {
                    ob_start();
                    include $layout_file;
                    $html .= ob_get_contents();
                    ob_end_clean();

                    return $html;
                } else {
                    return "Layout $layout_file not found !!!".BR;
                }

            } else {

                if(is_array($a_params['content'])) {
                    echo json_encode($a_params['content']);
                } else {
                    echo $a_params['content'];
                }
            }

            return '';
        }


        /**
         * Load layout config (JSON file)
         * @param $s_layout_id
         */
        protected function load_config($s_layout_id)
        {
            $_s_dir = CONFIG_LAYOUT_DIR . $s_layout_id;
            $config_file = "$_s_dir/$s_layout_id.json";

            if(file_exists($config_file))
            {
                $template_config = file_get_contents($config_file);
                $template_config = json_decode($template_config, true);

                $counter = 1;
                if(is_array($template_config['source']) && count($template_config['source'])) {
                    foreach($template_config['source'] as $source)
                    {
                        $counter++;
                        switch($source['type'])
                        {
                            case 'css':
                                if(!substr_count($source['href'], "http:", 0)) {
                                    $source['href'] = $_s_dir.'/'.$source['href'];
                                }

                                css::config("template-css-$counter", array('relative_dir' => $_s_dir));
                                css::add("template-css-$counter", $source);
                                break;

                            case 'js':
                                $script_id = isset($source['id'])           ? $source['id']            : null;
                                $param     = isset($source['param'])        ? $source['param']         : null;
                                $php_parse = isset($source['php_parse'])    ? $source['php_parse']     : true;
                                $group     = isset($source['group'])        ? $source['group']         : 'end';
                                $lang_dir  = isset($source['language_dir']) ? $source['language_dir']  : null;

                                if(isset($source['global']) &&
                                    $source['global'] == true) {
                                    javascript::add($script_id, $source['src'], $group, $param, $lang_dir, $php_parse);
                                }
                                else
                                    javascript::add($script_id, $_s_dir.'/'.$source['src'], $group, $param, $lang_dir, $php_parse);
                                break;
                        }
                    }
                }
            }
        }
    }

