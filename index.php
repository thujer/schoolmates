<?php

    define('RUNTIME_TEST', 'true');

    require_once 'config/app_config.php';
    require_once CONFIG_CORE_DIR . DS . 'core.php';
    require_once CONFIG_CORE_DIR . DS . 'autoload.php';
    require_once CONFIG_CORE_DIR . DS . 'request.php';
    require_once CONFIG_CORE_DIR . DS . 'css.php';

    $core = new Runtime\core();
    $core->process();
    $core->done();
