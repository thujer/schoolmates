<?php

    namespace Runtime;

    /**
     * Controller class
     * @author Tomas Hujer
     */
    class controller {

        var $a_template;

        var $s_controller;
        var $s_action;
        var $a_item;

        /**
         * Init controller
         * @param string $s_controller
         * @param string $s_action
         * @param array $a_item
         */
        public function __construct($s_controller, $s_action, $a_item = array()) {
            $this->s_controller = $s_controller;
            $this->s_action = $s_action;
            $this->a_item = $a_item;
        }


        /**
         * Get previously set controller and action
         * @return string
         */
        protected function get() {
            return $this->s_controller.':'.$this->s_action;
        }


        /**
         * Generate html from template
         * @param array $a_template_var
         * @return string html content
         */
        protected function render_view($a_template_var = array()) {

            $html = '';

            if (is_array($a_template_var) && count($a_template_var)) {
                foreach($a_template_var as $key => $value) {
                    $this->a_template[$key] = $value;
                }
            }

            $template_file = CONFIG_APP_DIR . "/templates/{$this->s_controller}/{$this->s_action}.php";
            if (file_exists($template_file)) {

                $css_file = CONFIG_APP_DIR . "/templates/{$this->s_controller}/css/{$this->s_action}.css";
                if (file_exists($css_file))
                    css::add($this->s_action, $css_file);

                $js_file = CONFIG_APP_DIR . "/templates/{$this->s_controller}/js/{$this->s_action}.js";
                if (file_exists($js_file))
                    javascript::add($this->s_action, $js_file);

                ob_start();
                include $template_file;
                $html .= ob_get_contents();
                ob_end_clean();
            }
            else
                $html .= "Template $template_file not found !!!" . BR;

            return $html;
        }

    }

