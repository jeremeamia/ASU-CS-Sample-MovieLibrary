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
		foreach ($results->xpath('/catalog_titles/catalog_title') as $movie_data)
		{
			$movies[] = $this->_parseMovieData($movie_data);
		}

		return $movies;
	}

	public function getMovie($url_id)
	{
		$results = $this->_sendNetflixRequest($url_id);

		return $this->_parseMovieData($results);
	}

	protected function _sendNetflixRequest($path, array $parameters = array())
	{
		// Create the signature object
		$signature = $this->_oauth->sign(array(
			'path' => $path,
			'parameters' => array_merge($parameters, array('format' => 'xml')),
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

		return simplexml_load_string($results);
	}

	protected function _parseMovieData(SimpleXMLElement $movie_data)
	{
		// Parse categories
		$categories_size = 0;
		$categories = array();
		foreach ($movie_data->category as $category)
		{
			$label = (string) $category->attributes()->label;
			$categories_size += strlen($label) + 1;
			if ($categories_size <= 200)
			{
				$categories[] = $label;
			}
		}

		// Build an array of relevant movie data
		return (object) array(
			'netflix_id'  => (string) $movie_data->id,
			'title'       => (string) $movie_data->title->attributes()->regular,
			'year'        => (string) $movie_data->release_year,
			'mpaa_rating' => array_shift($categories),
			'categories'  => $categories,
			'user_rating' => (string) $movie_data->average_rating,
			'box_art'     => (string) $movie_data->box_art->attributes()->large,
		);
	}
}
