<?php

    namespace Runtime;

    if(!defined('RUNTIME_TEST') &&
      (!defined('DIRECT_LOAD')))
        die('Unauthorized access !');


    /**
     * Class css
     * Manage css files content
     */
    class css
    {
        public static $css_files;
        public static $css_params;

        /**
         * Generate CSS by library generator, set sessions and insert load tag into HTML code
         * @param string $css_id CSS Identification string
         * @param array $css_attr File attributes (src, media,...)
         */
        static public function add($css_id, $css_attr = array())
        {
            if(!is_array(self::$css_files))
                self::$css_files = array();

            self::$css_files[$css_id] = $css_attr;
        }


        static public function config($css_id, $css_params_array)
        {
            if(is_array($css_params_array))
            {
                foreach($css_params_array as $key => $css_params)
                {
                    self::$css_params[$css_id][$key] = $css_params;
                }
            }
        }


        /**
         * Generate CSS by library generator, set sessions and insert load tag into HTML code
         * @return string
         */
        static public function load_styles()
        {
            $html = '';

            // TODO: zkontrolovat variantu INLINE !!!
            if(CONFIG_INLINE_CSS)
            {
                $html .= "<style type=\"text/css\" charset=\"utf-8\">".LN;

                if(!empty(css::$css_params[$css_id]))
                {
                    foreach(css::$css_params[$css_id] as $param_id => $param_value)
                    {
                        eval("$$param_id = \"$param_value\"");
                    }
                }


                foreach(self::$css_files as $css_id => $css_file)
                {
                    $html .= LN.'/* ----------------- '.$css_id.' / '.$css_file.' -------------------- */'.LN;

                    $file = $css_file;
                    if(file_exists($file))
                    {
                        ob_start();
                        include $file;
                        $html .= ob_get_contents();
                        ob_end_clean();
                    }
                    else
                        $html .= "<!-- <not found: ".$file."> -->".LN;
                }

                $html .= LN."</style>".LN;
            }
            else
            {
                if(is_array(self::$css_files) && count(self::$css_files)) {
                    foreach(self::$css_files as $css_id => $css_file_attr)
                    {
                        $params = '';
                        if(!empty(css::$css_params[$css_id])) {
                            foreach(css::$css_params[$css_id] as $param_id => $param_value) {
                                $params = "&$param_id=$param_value";
                            }
                        }

                        if(!substr_count($css_file_attr['href'], "http:", 0)) {
                            if($css_file_attr['php_parse'])
                                $css_file_attr['href'] = "/css.php?id={$css_file_attr['href']}$params";
                            else
                                $css_file_attr['href'] = "{$css_file_attr['href']}";
                        }

                        $html .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"{$css_file_attr['media']}\" href=\"{$css_file_attr['href']}\" >".LN;
                    }
                }
            }

            return($html);
        }

    }

