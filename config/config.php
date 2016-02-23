<?php

Config::set('site_name', 'Проект DOCbase');

Config::set('languages', array('ua', 'ru'));

Config::set('default_group', 'cg_kalita');
Config::set('default_enterprise', 'akkalita');

// Routes. Route name => method prefix
Config::set('routes', array(
    'default' => '',
    'admin'   => 'admin_',
    'ajax' => "ajax_"
));

Config::set('default_route', 'default');
Config::set('default_language', 'ua');
Config::set('default_controller', 'users');
Config::set('default_action', 'login');

Config::set('db.host', 'localhost');
Config::set('db.user', 'root');
Config::set('db.password', 'nopass');
Config::set('db.db_name', 'docbase');