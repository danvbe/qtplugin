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
	 * The API Url as gotten from user
	 *
	 * @var string $api_url
	 */
	private $api_url;

	/**
	 * The security nonce
	 *
	 * @var string
	 */
	private $_nonce = 'qtplugin_admin';
	/**
	 * The option name
	 *
	 * @var string
	 */
	private $option_name = 'qtplugin_data';


	/**
	 * QTPlugin constructor.
	 *
	 * The main plugin actions registered for WordPress
	 */
	public function __construct()
	{
		//to get the options from the db
		$this->getData();

		add_action( 'wp_footer', array( $this, 'footerRandomQuote' ) );

		// Admin page calls:
		add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
		add_action( 'wp_ajax_store_admin_data', array( $this, 'storeAdminData' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScripts' ) );
	}

	/**
	 * Returns the saved options data as an array
	 *
	 * @return array
	 */
	private function getData()
	{
		$options = get_option($this->option_name, array());
		$this->api_url = $options['api_url'];

		return $options;
	}

	/**
	 * Helper function to construct the proper URL and to avoid adding the qt_page param several times
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
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts()
	{
		wp_enqueue_script('qtplugin-admin', QTPLUGIN_URL. '/assets/js/admin.js', array(), 1.0);
		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
		);
		wp_localize_script('qtplugin-admin', 'qtplugin_exchanger', $admin_options);
	}

	/**
	 * Callback for the Ajax request
	 *
	 * Updates the options data
	 *
	 * @return void
	 */
	public function storeAdminData()
	{

		if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
			die('Invalid Request!');

		$data = $this->getData();

		foreach ($_POST as $field=>$value) {

			if (substr($field, 0, 9) !== "qtplugin_" || empty($value))
				continue;

			// We remove the qtplugin_ prefix to clean things up
			$field = substr($field, 9);

			$data[$field] = $value;

		}

		update_option($this->option_name, $data);

		echo __('Saved!', 'qtplugin');
		die();

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
		$quote =QTPlugin_API::getRandomQuote($this->api_url);

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
		$data = $this->getData();
		QTPlugin_Forms::getConfigurations($data);

		if (!empty($data['api_url'])) {
			$page = isset( $_GET['qtp_page'] ) ? $_GET['qtp_page'] : 'index';
			switch ( $page ) {
				case 'new':
					if ( isset( $_POST['author'] ) ) {
						$response = QTPlugin_API::addQuote($this->api_url, $_POST );
						if ( ! is_wp_error( $response ) ) {
							$response_data = json_decode( $response['body'], true );
							QTPlugin_Forms::showQuote( $response_data['id'] );
							unset( $_POST );
						} else {
							QTPlugin_Forms::newQuote( $_POST );
						}
					} else {
						QTPlugin_Forms::newQuote();
					}
					break;
				case 'edit':
					if ( isset( $_POST['id'] ) ) {
						$response = QTPlugin_API::editQuote($this->api_url, $_POST );
						if ( ! is_wp_error( $response ) ) {
							wp_redirect( QTPlugin::getURL() . '&qtp_page=show&id=' . $_POST['id'] );
							exit;
						} else {
							$quote = QTPlugin_API::getQuote($this->api_url, $_POST['id'] );
							QTPlugin_Forms::editQuote( $quote );
						}
					} else {
						$id = isset( $_GET['qtp_id'] ) ? $_GET['qtp_id'] : null;
						$quote = QTPlugin_API::getQuote($this->api_url, $id);
						QTPlugin_Forms::editQuote( $quote );
					}
					break;
					break;
				case 'show':
					$id = isset( $_GET['qtp_id'] ) ? $_GET['qtp_id'] : null;
					$quote = QTPlugin_API::getQuote($this->api_url, $id);
					QTPlugin_Forms::showQuote( $quote );
					break;
				case 'delete':
					$id       = isset( $_GET['qtp_id'] ) ? $_GET['qtp_id'] : null;
					$response = QTPlugin_API::deleteQuote($this->api_url, $id );
					if ( ! is_wp_error( $response ) ) {
						wp_redirect( QTPlugin::getURL() . '&qtp_page=list' );
						exit;
					} else {
						$quote = QTPlugin_API::getQuote($this->api_url, $id);
						QTPlugin_Forms::showQuote( $quote );
					}
					break;
				default:
					$quotes = QTPlugin_API::getQuotes($this->api_url);
					QTPlugin_Forms::listQuotes($quotes);
					break;
			}
		}
	}

}

/*
 * Starts our plugin class, easy!
 */
new QTPlugin();