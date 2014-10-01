<?php

    /**
     * Autoload definition - server try loads undefined function automatically
     * file must have the same name as the class which is defined in
     * @param $function_name
     */
    class autoload {

        /**
         * Add $dir into searched paths
         * @param $a_dirs directory name array (relative path from web root)
         */
        public static function path_add($a_dirs)
        {
            if(!defined('ABSOLUTE_PATH'))
                define ('ABSOLUTE_PATH', realpath('.'));

            if(!defined('DS'))
                define ('DS',      DIRECTORY_SEPARATOR);

            if(!defined('PS'))
                define ('PS',      PATH_SEPARATOR);

            if(is_array($a_dirs) && count($a_dirs))
            {
                foreach($a_dirs as $_s_dir) {
                    set_include_path(get_include_path().PS.ABSOLUTE_PATH.DS.$_s_dir);
                }
            }
        }


        /**
         * Try to include missing class file
         * @param $namespace
         */
        public static function load($namespace) {

            $splitpath = explode('\\', $namespace);

            $filename = array_pop($splitpath).'.php';   // Separate last item (class name)

            $result = @include $filename;               // Can't catch any exception
            if(!$result)
            {
                if(CONFIG_DEBUG)
                    echo BR."ERROR: Can't autoload class \"$namespace\" !".BR;
            }
        }
    }

    // Add paths into search path
    autoload::path_add(array('class', 'app', 'config', CONFIG_APP_CONTROLLERS, CONFIG_APP_CONFIG, CONFIG_APP_MODELS));

    // Register autoload function
    spl_autoload_register(__NAMESPACE__ . '\autoload::load');
