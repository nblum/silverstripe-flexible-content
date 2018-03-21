<?php
$dirname = dirname(__FILE__);
//define global path to Components' root folder
if (!defined('FLEXIBLE_CONTENT_PLUGIN_PATH')) {
    if (in_array('nblum', explode('/', $dirname))) {
        define('FLEXIBLE_CONTENT_PLUGIN_PATH', 'nblum/' . rtrim(basename($dirname)));
    } else {
        define('FLEXIBLE_CONTENT_PLUGIN_PATH', rtrim(basename($dirname)));
    }
}