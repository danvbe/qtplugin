<?php
/**
 * Plugin Name: Quote Test Plugin
 * Plugin URI:
 * Description: This is the very first plugin I ever created :)
 * Version: 1.0
 * Author: danvbe / Sergiu Barbus - don't ask ;)
 * Author email: danvbe@gmail.com
 **/

ob_clean();
ob_start();

/*
 * Plugin constants
 */
if(!defined('QTPLUGIN_URL'))
	define('QTPLUGIN_URL', plugin_dir_url( __FILE__ ));
if(!defined('QTPLUGIN_PATH'))
	define('QTPLUGIN_PATH', plugin_dir_path( __FILE__ ));

if(!defined('QTPLUGIN_API_URL'))
	define('QTPLUGIN_API_URL', 'http://127.0.0.1:8000/api/');

require_once( QTPLUGIN_PATH . 'class.qtplugin-api.php' );
require_once( QTPLUGIN_PATH . 'class.qtplugin-forms.php' );

/*
 * Main class
 */
/**
 * Class QTPlugin
 *
 * This class creates the option page and add the web app script
 */
class QTPlugin
{
	/**
	 * @var QTPlugin_API
	 */
	private $qtplugin_api;

	/**
	 * QTPlugin constructor.
	 *
	 * The main plugin actions registered for WordPress
	 */
	public function __construct()
	{
		add_action( 'wp_footer', array( $this, 'footerRandomQuote' ) );

		// Admin page calls:
		add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
		//add_action( 'wp_ajax_store_admin_data', array( $this, 'storeAdminData' ) );
		//add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScripts' ) );
	}

	/**
	 * @return string
	 */
	public static function getURL(){
		$string_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$array_url = parse_url($string_url);
		parse_str($array_url['query'],$queries);
		unset($queries['qtp_page']);
		$array_url['query'] = $queries;
		$ret_query = http_build_query($queries);
		return $array_url['scheme'].'://'.$array_url['host'].$array_url['path'].'?'.$ret_query;
	}

	/**
	 * Adds the QTPlugin label to the WordPress Admin Sidebar Menu
	 */
	public function addAdminMenu()
	{
		add_menu_page(
			__( 'QTPlugin', 'qtplugin' ),
			__( 'QTPlugin', 'qtplugin' ),
			'manage_options',
			'qtplugin',
			array($this, 'adminLayout'),
			''
		);
	}


	// This just echoes the text
	public function footerRandomQuote() {
		$quote =QTPlugin_API::getRandomQuote();

		echo '<div style="background: green; color: white; text-align: center;">';
		echo $quote['text'].' - <b>'.$quote['author'].'</b>';
		echo '</div>';
	}

	/**
	 * Outputs the Admin Dashboard layout containing the form with all its options
	 *
	 * @return void
	 */
	public function adminLayout()
	{
		$page = isset($_GET['qtp_page'])?$_GET['qtp_page']:'index';
		switch ($page) {
			case 'new':
				if(isset($_POST['author'])){
					$response = QTPlugin_API::addQuote($_POST);
					if(!is_wp_error($response)){
						$response_data = json_decode($response['body'], true);
						QTPlugin_Forms::showQuote($response_data['id']);
						unset($_POST);
					}
					else {
						QTPlugin_Forms::newQuote( $_POST );
					}
				} else {
					QTPlugin_Forms::newQuote();
				}
				break;
			case 'edit':
				if(isset($_POST['id'])){
					$response = QTPlugin_API::editQuote($_POST);
					if(!is_wp_error($response)){
						wp_redirect(QTPlugin::getURL().'&qtp_page=show&id='.$_POST['id']);
						exit;
					}
					else {
						QTPlugin_Forms::editQuote( $_POST['id'] );
					}
				} else {
					$id = isset($_GET['qtp_id'])?$_GET['qtp_id']:null;
					QTPlugin_Forms::editQuote($id);
				}
				break;
				break;
			case 'show':
				$id = isset($_GET['qtp_id'])?$_GET['qtp_id']:null;
				QTPlugin_Forms::showQuote($id);
				break;
			case 'delete':
				$id = isset($_GET['qtp_id'])?$_GET['qtp_id']:null;
				$response = QTPlugin_API::deleteQuote($id);
				if(!is_wp_error($response)){
					wp_redirect(QTPlugin::getURL().'&qtp_page=list');
					exit;
				}
				else {
					QTPlugin_Forms::showQuote($id);
				}
				break;
			default:
				QTPlugin_Forms::listQuotes();
				break;
		}
	}

}

/*
 * Starts our plugin class, easy!
 */
new QTPlugin();