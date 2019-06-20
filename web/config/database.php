<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (IA_ROOT.'/data/config.php');
$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $config['db']['master']['host'],
	'username' => $config['db']['master']['username'],
	'password' => $config['db']['master']['password'],
	'database' => $config['db']['master']['database'],
	'dbdriver' => 'mysqli',
	'dbprefix' => $config['db']['master']['tablepre'],
	'pconnect' => $config['db']['master']['pconnect'],
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => $config['db']['master']['charset'],
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
