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
	 * Get all the authors
	 *
	 * @return array
	 */
	public function getAuthors()
	{
		$data = array();
		$response = wp_remote_get($this->api_url.'quote/'.$this->app_id.'/authors');

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}

		return $data;

	}

	/**
	 * Get quotes of a given author
	 *
	 * @return array
	 */
	public function getAuthorQuotes($author)
	{
		$data = array();
		$response = wp_remote_get($this->api_url.'quote/'.$this->app_id.'/author/'.urlencode($author));

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}

		return $data;

	}

	/**
	 * Get random quote
	 *
	 * @return array
	 */
	public function getRandomQuote()
	{
		$data = array();
		$response = wp_remote_get($this->api_url.'quote/random/'.$this->app_id);

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
		$response = wp_remote_get($this->api_url.'quote/'.$this->app_id);

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
		$response = wp_remote_get($this->api_url.'quote/'.$this->app_id.'/'.$id);

		if (!is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}

		return $data;
	}

	public function addQuote($data){
		$response = wp_remote_request($this->api_url.'quote/'.$this->app_id,array(
				'method' => 'POST',
				'body' => $data,
			)
		);

		return $response;
	}

	public function deleteQuote($id){
		$response = wp_remote_request($this->api_url.'quote/'.$this->app_id.'/'.$id,array(
				'method' => 'DELETE',
			)
		);

		return $response;
	}

	public function editQuote($data){
		$response = wp_remote_request($this->api_url.'quote/'.$this->app_id.'/'.$data['id'],array(
				'method' => 'PUT',
				'body' => $data,
			)
		);

		return $response;
	}
}