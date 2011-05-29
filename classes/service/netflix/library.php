<?php defined('App::NAME') OR die('You cannot execute this script.');

class Service_Netflix_Library
{
	const PATH_CATALOG = 'http://api.netflix.com/catalog/titles';

	protected $_config;
	protected $_oauth;

	public function __construct(Service_Netflix_OAuth $oauth, Config $config)
	{
		$this->_config = $config;
		$this->_oauth = $oauth;
	}

	public function lookup($term)
	{
		$results = $this->_sendNetflixRequest(self::PATH_CATALOG, array(
			'term'        => $term,
			'expand'      => 'formats,synopsis',
			'max_results' => $this->_config->get('netflix', 'max_results'),
		));

		$movies = array();
		foreach ($results->catalog_titles->catalog_title as $movie_data)
		{
			$movies[] = $this->_parseMovieData($movie_data);
		}

		return $movies;
	}

	public function getMovie($url_id)
	{
		$results = $this->_sendNetflixRequest($url_id);
		return $this->_parseMovieData($results->catalog_title);
	}

	protected function _sendNetflixRequest($path, array $parameters = array())
	{
		// Build parameters
		$parameters = array_merge($parameters, array(
			'output' => 'json',
		));

		// Create the signature object
		$signature = $this->_oauth->sign(array(
			'path' => $path,
			'parameters' => $parameters,
			'signatures' => array(
				'consumer_key'  => $this->_config->get('netflix', 'api_key'),
				'shared_secret' => $this->_config->get('netflix', 'secret_key'),
			),
		));

		// Fetch the data
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $signature['signed_url']);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($curl, CURLOPT_TIMEOUT, 2);
		$results = curl_exec($curl);
		if (curl_errno($curl))
		{
			throw new RuntimeException('The cURL request failed to be executed.');
		}

		return json_decode($results);
	}

	protected function _parseMovieData($movie_data)
	{
		$movie = new stdClass();
		$movie->netflix_id = $movie_data->id;
		$movie->title = $movie_data->title->regular;
		$movie->year = $movie_data->release_year;
		$movie->mpaa_rating = array_shift($movie_data->category)->label;
		$movie->categories = array_map(function($category) {
			return $category->label;
		}, $movie_data->category);
		$movie->user_rating = $movie_data->average_rating;
		$movie->box_art = $movie_data->box_art->large;
		return $movie;
	}
}
