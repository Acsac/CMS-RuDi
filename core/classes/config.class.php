<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.5                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2014                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

class cmsConfig {
    private static $instance = null;
    private static $config = array();

    private function __construct() {
        mb_internal_encoding("UTF-8");

        self::$config = self::getDefaultConfig();

        date_default_timezone_set(self::$config['timezone']);

        setlocale(LC_ALL, "ru_RU.UTF-8");

        return true;
    }

    private function __clone() {}

    public function __get($name) {
        return self::$config[$name];
    }
    public function __set($name, $value){
        self::$config[$name] = $value;
    }
    public function __isset($name){
        return isset(self::$config[$name]);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Возвращает оригинальный массив конфигурации системы
     * отдельно используется только в админке и при установке
     * @return array
     */
    public static function getDefaultConfig() {
        $d_cfg = array(
            'scheme'             => '',
            'cache'              => 1,
            'cache_time'         => 1800,
            'cache_type'         => 'file',
            'memcached_host'     => 'localhost',
            'memcached_port'     => 11211,
            'sitename'           => '',
            'title_and_sitename' => 1,
            'title_and_page'     => 1,
            'hometitle'          => '',
            'homecom'            => '',
            'siteoff'            => 0,
            'only_authorized'    => 0,
            'debug'              => 0,
            'offtext'            => '',
            'keywords'           => '',
            'metadesc'           => '',
            'lang'               => 'ru',
            'is_change_lang'     => 0,
            'sitemail'           => '',
            'sitemail_name'      => '',
            'wmark'              => 'watermark.png',
            'admin_template'     => 'admin/_default_',
            'template'           => '_default_',
            'combine_js_enable'  => 0,
            'combine_js'         => '',
            'combine_css_enable' => 0,
            'combine_css'        => '',
            'com_without_name_in_url' => 'content',
            'splash'             => 0,
            'slight'             => 1,
            'db_host'            => '',
            'db_base'            => '',
            'db_user'            => '',
            'db_pass'            => '',
            'db_prefix'          => 'cms',
            'show_pw'            => 1,
            'last_item_pw'       => 1,
            'index_pw'           => 0,
            'fastcfg'            => 1,
            'mailer'             => 'mail',
            'smtpsecure'         => '',
            'smtpauth'           => 0,
            'smtpuser'           => '',
            'smtppass'           => '',
            'smtphost'           => 'localhost',
            'smtpport'           => 25,
            'timezone'           => 'Europe/Moscow',
            'user_stats'         => 1,
            'seo_url_count'      => 0,
            'max_pagebar_links'  => 10,
            'allow_ip'           => '',
            'iframe_enable'      => 0,
            'vk_enable'          => 0,
            'vk_id'              => '',
            'vk_private_key'     => '',
            'JevixAllowTags'     => 'p,a,img,i,b,u,s,strike,video,em,strong,nobr,li,ol,ul,div,abbr,sup,sub,acronym,h1,h2,h3,h4,h5,h6,br,hr,pre,code,object,param,embed,blockquote,iframe,span,input,table,caption,th,tr,td,article,nav,audio,menu,section,time',
            'JevixTagCutWithContent' => 'script,style,meta'
        );

        $f = PATH .'/includes/config/config.inc.json';

        if (file_exists($f)) {
            $_CFG = json_decode(trim(file_get_contents($f)), true);
        } else {
            $_CFG = array();
        }

        $cfg = array_merge($d_cfg, $_CFG);

        foreach ($cfg as $key => $value) {
            $cfg[$key] = stripslashes($value);
        }
        
        $cfg['cookie_key'] = md5($cfg['sitename']);

        return $cfg;
    }

    /**
     * Возвращает значение опции конфигурации
     * или полный массив значений
     * @param str $value
     * @return mixed
     */
    public static function getConfig($value = '') {
        if ($value) {
            if (isset(self::$config[$value])) {
                return self::$config[$value];
            } else {
                return null;
            }
        } else {
            return self::$config;
        }
    }

    /**
     * Сохраняет массив в файл конфигурации
     * @param array $_CFG
     */
    public static function saveToFile($_CFG, $file='config.inc.json') {
        global $_LANG;
        $filepath = PATH .'/includes/config/'. $file;

        if (file_exists($filepath)) {
            if (!@is_writable($filepath)) {
                die(sprintf($_LANG['FILE_NOT_WRITABLE'], '/includes/config/'. $file));
            }
        } else {
            if (!@is_writable(dirname($filepath))) {
                die(sprintf($_LANG['DIR_NOT_WRITABLE'], '/includes/config'));
            }
        }
        
        file_put_contents($filepath, cmsCore::jsonEncode($_CFG, true));

        return true;
    }
}