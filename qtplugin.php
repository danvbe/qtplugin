<?php
/**
 * Plugin Name: QuoTest Plugin
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
require_once( QTPLUGIN_PATH . 'class.qtplugin-html.php' );

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
	 * This is the class responsible for communicating with the API client
	 *
	 * @var QTPlugin_API
	 */
	private $api_class;

	/**
	 * This is the class responsible for rendering html content
	 *
	 * @var QTPlugin_HTML
	 */
	private $html_class;

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
		//the API class needs the configuration data to run
		$this->api_class = new QTPlugin_API($this->getData());
		$this->html_class = new QTPlugin_HTML();

		add_action( 'wp_footer', array( $this, 'footerDisplay' ) );

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
		return get_option($this->option_name, array());
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
		//we enque jQueryUI for Autocomplete availability
		$wp_scripts = wp_scripts();
		wp_enqueue_style( 'jquery-ui-styles',
			sprintf('https://code.jquery.com/ui/%s/themes/black-tie/jquery-ui.css',
				$wp_scripts->registered['jquery-ui-core']->ver)
		);
		wp_enqueue_style( 'demo-styles','https://jqueryui.com/resources/demos/style.css');
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );

		//enque our custom code
		wp_enqueue_script('qtplugin-admin', QTPLUGIN_URL. '/assets/js/admin.js');

		//construct the variables to be passed
		$authors = $this->api_class->getAuthors();
		$arr_authors = [];
		foreach ($authors as $author){
			$arr_authors[] = $author['author'];
		}

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
			'authors'  => $arr_authors,
		);
		wp_localize_script('qtplugin-admin', 'qtplugin_php_vars', $admin_options);
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

	/**
	 * This displays the footer page in the frontend part
	 */
	public function footerDisplay(){
		echo '<div style="background: lightgray; color: #010101; text-align: center;">';
		global $wp;
		$query = $_GET;
		if(array_key_exists('qt_author',$query)){
			$author = urldecode($query['qt_author']);
			$quotes =$this->api_class->getAuthorQuotes($author);
			echo '<u>Quotes by <em>"'.$author.'"</em></u>:<br>';
			foreach ($quotes as $quote){
				echo $quote['text'].'<br>';
			}
			unset($query['qt_author']);
			$href = home_url(add_query_arg(array($query), $wp->request));
			echo '<b><a href="'.$href.'">Back to random quotes</a></b>';
		} else {
			$quote =$this->api_class->getRandomQuote();
			$query['qt_author'] = urlencode($quote['author']);
			$href = home_url(add_query_arg(array($query), $wp->request));
			echo $quote['text'].' - <b><a href="'.$href.'">'.$quote['author'].'</a></b>';
		}
		echo '</div>';
	}

	/**
	 * Outputs the Admin Dashboard layout
	 *
	 * @return void
	 */
	public function adminLayout()
	{
		$data = $this->getData();
		$this->html_class->configurationForm($data);

		if (!empty($data['api_url'])) {
			$page = isset( $_GET['qtp_page'] ) ? $_GET['qtp_page'] : 'index';
			switch ( $page ) {
				case 'new':
					if ( isset( $_POST['author'] ) ) {
						$response = $this->api_class->addQuote( $_POST );
						$data = json_decode($response['body'], true);
						if ( ! is_wp_error( $response ) && !array_key_exists('errors',$data)) {
							wp_redirect( QTPlugin::getURL() . '&qtp_page=show&id=' . $_POST['id'] );
							exit;
						} else {
							$this->html_class->newQuoteForm( $_POST, $data['errors'] );
						}
					} else {
						$this->html_class->newQuoteForm();
					}
					break;
				case 'edit':
					if ( isset( $_POST['id'] ) ) {
						$response = $this->api_class->editQuote( $_POST );
						$data = json_decode($response['body'], true);
						if ( ! is_wp_error( $response && !array_key_exists('errors',$data) ) ) {
							wp_redirect( QTPlugin::getURL() . '&qtp_page=show&id=' . $_POST['id'] );
							exit;
						} else {
							$quote = $this->api_class->getQuote( $_POST['id'] );
							$this->html_class->editQuoteForm( $quote, $data['errors'] );
						}
					} else {
						$id = isset( $_GET['qtp_id'] ) ? $_GET['qtp_id'] : null;
						$quote = $this->api_class->getQuote( $id );
						$this->html_class->editQuoteForm( $quote );
					}
					break;
					break;
				case 'show':
					$id = isset( $_GET['qtp_id'] ) ? $_GET['qtp_id'] : null;
					$quote = $this->api_class->getQuote( $id );
					$this->html_class->showQuote( $quote );
					break;
				case 'delete':
					$id       = isset( $_GET['qtp_id'] ) ? $_GET['qtp_id'] : null;
					$response = $this->api_class->deleteQuote( $id );
					$data = json_decode($response['body'], true);
					if ( ! is_wp_error( $response && !array_key_exists('errors',$data)) ) {
						wp_redirect( QTPlugin::getURL() . '&qtp_page=list' );
						exit;
					} else {
						$quote = $this->api_class->getQuote( $id );
						$this->html_class->showQuote( $quote );
					}
					break;
				default:
					$quotes = $this->api_class->getQuotes();
					$this->html_class->quotesList($quotes);
					break;
			}
		}
	}

}

/*
 * Start our plugin class!
 */
new QTPlugin();