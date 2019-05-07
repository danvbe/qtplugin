<?php
/**
 * Created by PhpStorm.
 * User: ubuntu
 * Date: 5/7/19
 * Time: 11:45 AM
 */

/**
 * Class QTPlugin_API
 * Make API calls to the QTSymfony API and returns the response
 */
class QTPlugin_API {

	/**
	 * Get random quote
	 *
	 * @return array
	 */
	public static function getRandomQuote()
	{
		$data = array();
		$response = wp_remote_get(QTPLUGIN_API_URL.'randomquote');

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}

		return $data;

	}

	/**
	 * Get list of quotes
	 *
	 * @return array
	 */
	public static function getQuotes()
	{
		$data = array();
		$response = wp_remote_get(QTPLUGIN_API_URL.'quote');

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}


		return $data;

	}

	/**
	 * Get list of quotes
	 *
	 * @return array
	 */
	public static function getQuote($id)
	{
		$data = array();
		$response = wp_remote_get(QTPLUGIN_API_URL.'quote/'.$id);

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}


		return $data;
	}

	public static function addQuote($data){
		$response = wp_remote_post(QTPLUGIN_API_URL.'quote/new',array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $data,
				'cookies' => array()
			)
		);

		return $response;
	}

	public static function deleteQuote($id){
		$response = wp_remote_post(QTPLUGIN_API_URL.'quote/delete',array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array('id'=>$id),
				'cookies' => array()
			)
		);

		return $response;
	}

	public static function editQuote($data){
		$response = wp_remote_post(QTPLUGIN_API_URL.'quote/'.$data['id'],array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $data,
				'cookies' => array()
			)
		);

		return $response;
	}
}