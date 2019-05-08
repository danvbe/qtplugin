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
	private $app_id;

	public function __construct($data) {
		$this->api_url = $data['api_url'];
		$this->app_id = $data['app_id'];
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
	 * Gets a specific Quote
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
		$response = wp_remote_request($this->api_url.'quote',array(
				'method' => 'POST',
				'body' => $data,
			)
		);

		return $response;
	}

	public function deleteQuote($id){
		$response = wp_remote_request($this->api_url.'quote/'.$id,array(
				'method' => 'DELETE',
			)
		);

		return $response;
	}

	public function editQuote($data){
		$response = wp_remote_request($this->api_url.'quote/'.$data['id'],array(
				'method' => 'PUT',
				'body' => $data,
			)
		);

		return $response;
	}
}