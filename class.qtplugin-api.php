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
	public static function getRandomQuote($api_url)
	{
		$data = array();
		$response = wp_remote_get($api_url.'randomquote');

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
	public static function getQuotes($api_url)
	{
		$data = array();
		$response = wp_remote_get($api_url.'quote');

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
	public static function getQuote($api_url, $id)
	{
		$data = array();
		$response = wp_remote_get($api_url.'quote/'.$id);

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}


		return $data;
	}

	public static function addQuote($api_url, $data){
		$response = wp_remote_post($api_url.'quote/new',array(
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

	public static function deleteQuote($api_url, $id){
		$response = wp_remote_post($api_url.'quote/delete',array(
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

	public static function editQuote($api_url, $data){
		$response = wp_remote_post($api_url.'quote/'.$data['id'],array(
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