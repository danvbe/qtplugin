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

	private $api_url;

	public function __construct($data) {
		$this->api_url = $data['api_url'];
	}

	/**
	 * Get random quote
	 *
	 * @return array
	 */
	public function getRandomQuote()
	{
		$data = array();
		$response = wp_remote_get($this->api_url.'randomquote');

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
	public function getQuotes()
	{
		$data = array();
		$response = wp_remote_get($this->api_url.'quote');

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
	public function getQuote($id)
	{
		$data = array();
		$response = wp_remote_get($this->api_url.'quote/'.$id);

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}


		return $data;
	}

	public function addQuote($data){
		$response = wp_remote_post($this->api_url.'quote/new',array(
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

	public function deleteQuote($id){
		$response = wp_remote_post($this->api_url.'quote/delete',array(
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

	public function editQuote($data){
		$response = wp_remote_post($this->api_url.'quote/'.$data['id'],array(
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