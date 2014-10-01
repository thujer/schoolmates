<?php

    namespace Runtime;

    if(!defined('RUNTIME_TEST') &&
      (!defined('DIRECT_LOAD')))
        die('Unauthorized access !');

    class javascript
    {
        public static $js_files;
        public static $js_params;

        /**
         * Generate JavaScript code by script generator set sessions and insert load tag into HTML code
         * If $js_file contains .js, then only insert load tag into HTML code
         * @param $js_id
         * @param $js_file
         * @param string $group
         * @param array $params
         * @param string $lang_dir
         * @param bool $php_parse
         */
        static public function add($js_id, $js_file, $group = 'end', $params = array(), $lang_dir = '', $php_parse = false)
        {
            if(!is_array(self::$js_files))
                self::$js_files = array();

            if(!in_array($js_file, self::$js_files))
            {
                self::$js_files[$group][$js_id]['file'] = $js_file;
                self::$js_files[$group][$js_id]['php_parse'] = $php_parse;
            }

            // If defined language mutation
            if(!empty($lang_dir))
            {
                self::$js_files[$group][$js_id]['lang_dir'] = $lang_dir;
            }

            // Write parameters
            if(is_array($params))
            {
                foreach ($params as $key => $param)
                {
                    self::$js_files[$group][$js_id]['params'][$key] = $param;
                }
            }
        }


        /**
         * Generate CSS by library generator, set sessions and insert load tag into HTML code
         * @param $group
         * @return string
         */
        static public function load_scripts($group)
        {
            $html = '';

            if(CONFIG_INLINE_JS)
            {
                $html .= "<script type=\"text/javascript\" charset=\"utf-8\"> <!-- group: $group -->".LN;

                foreach(self::$js_files[$group] as $js_id => $js_file)
                {
                    $html .= LN.'/* ----------------- '.$js_id.' / '.$js_file['file'].' -------------------- */'.LN;
                    $params = $js_file['params'];
                    if(!empty($params))
                    {
                        foreach($params as $param_id => $param_value)
                        {
                            eval("$$param_id = \"$param_value\"");
                        }
                    }

                    $file = $js_file['file'];

                    if(file_exists($file))
                    {
                        ob_start();
                        include $file;
                        $js = ob_get_contents();
                        ob_end_clean();
                    }
                    else
                        $js = "<!-- <not found: ".$file."> -->".LN;

                    $html .= $js;
                }

                $html .= LN."</script>  <!-- end of group: $group -->".LN;
            }
            else {
                if(is_array(self::$js_files[$group]) &&
                    (!empty(self::$js_files[$group]))) {

                    foreach(self::$js_files[$group] as $js_id => $js_file)
                    {
                        $params = $js_file['params'];
                        if(!empty($params))
                            foreach($params as $param_id => $param_value)
                                $params = "&$param_id=$param_value";

                        $file = $js_file['file'];

                        if($js_file['php_parse']) {
                            $html .= "\t<script type=\"text/javascript\" charset=\"utf-8\" src=\"/js.php?id=$file$params\"></script>".LN;
                        } else {
                            $html .= "\t<script type=\"text/javascript\" charset=\"utf-8\" src=\"$file$params\"></script>".LN;
                        }
                    }
                }
            }

            return($html);
        }

    }
