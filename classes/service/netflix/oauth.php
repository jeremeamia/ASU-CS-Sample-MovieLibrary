<?php defined('App::NAME') OR die('You cannot execute this script.');

class Service_Netflix_OAuth_Exception extends Exception {}

class Service_Netflix_OAuth
{
	var $_secrets;
	var $_default_signature_method;
	var $_action;
	var $_nonce_chars;

	/* Simple OAuth
	 *
	 * This class only builds the OAuth elements, it does not do the actual
	 * transmission or reception of the tokens. It does not validate elements
	 * of the token. It is for client use only.
	 *
	 * api_key is the API key, also known as the OAuth consumer key
	 * shared_secret is the shared secret (duh).
	 *
	 * Both the api_key and shared_secret are generally provided by the site
	 * offering OAuth services. You need to specify them at object creation
	 * because nobody <explative>ing uses OAuth without that minimal set of
	 * signatures.
	 *
	 * If you want to use the higher order security that comes from the
	 * OAuth token (sorry, I don't provide the functions to fetch that because
	 * sites aren't horribly consistent about how they offer that), you need to
	 * pass those in either with .setTokensAndSecrets() or as an argument to the
	 * .sign() or .getHeaderString() functions.
	 *
	 * Example:
	   <code>
	   <?php
		$oauthObject = new Service_Netflix_OAuth();
		$result = $oauthObject->sign(Array('path'=>'http://example.com/rest/',
										   'parameters'=> 'foo=bar&gorp=banana',
										   'signatures'=> Array(
												'api_key'=>'12345abcd',
												'shared_secret'=>'xyz-5309'
											 )));
		?>
		<a href="<?php print $result['signed_url']; ?>">Some Link</a>;
	   </code>
	 *
	 * that will sign as a "GET" using "SHA1-MAC" the url. If you need more than
	 * that, read on, McDuff.
	 */

	/** Service_Netflix_OAuth creator
	 *
	 * Create an instance of Service_Netflix_OAuth
	 *
	 * @param api_key {string}       The API Key (sometimes referred to as the consumer key) This value is usually supplied by the site you wish to use.
	 * @param shared_secret (string) The shared secret. This value is also usually provided by the site you wish to use.
	 */
	function Service_Netflix_OAuth ($APIKey = "",$sharedSecret=""){
		if (!empty($APIKey))
			$this->_secrets{'consumer_key'}=$APIKey;
		if (!empty($sharedSecret))
			$this->_secrets{'shared_secret'}=$sharedSecret;
		$this->_default_signature_method="HMAC-SHA1";
		$this->_action="GET";
		$this->_nonce_chars="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		return $this;
	}

	/** reset the parameters and url
	*
	*/
	function reset() {
		$this->_parameters=null;
		$this->path=null;
		return $this;
	}

	/** set the parameters either from a hash or a string
	*
	* @param {string,object} List of parameters for the call, this can either be a URI string (e.g. "foo=bar&gorp=banana" or an object/hash)
	*/
	function setParameters ($parameters=Array()) {

		if (is_string($parameters))
			$parameters = $this->_parseParameterString($parameters);
		if (empty($this->_parameters))
			$this->_parameters = $parameters;
		elseif (!empty($parameters))
			$this->_parameters = array_merge($this->_parameters,$parameters);
		if (empty($this->_parameters['oauth_nonce']))
			$this->_getNonce();
		if (empty($this->_parameters['oauth_timestamp']))
			$this->_getTimeStamp();
		if (empty($this->_parameters['oauth_consumer_key']))
			$this->_getApiKey();
		if (empty($this->_parameters['oauth_token']))
			$this->_getAccessToken();
		if (empty($this->_parameters['oauth_signature_method']))
			$this->setSignatureMethod();
		if (empty($this->_parameters['oauth_version']))
			$this->_parameters['oauth_version']="1.0";
		//error_log('parameters: '.print_r($this,1));
		return $this;
	}

	// convienence method for setParameters
	function setQueryString ($parameters) {
		return $this->setParameters($parameters);
	}

	/** Set the target URL (does not include the parameters)
	*
	* @param path {string} the fully qualified URI (excluding query arguments) (e.g "http://example.org/foo")
	*/
	function setURL ($path) {
		if (empty($path))
			throw new Service_Netflix_OAuth_Exception('No path specified for Service_Netflix_OAuth.setURL');
		$this->_path=$path;
		return $this;
	}

	/** convienence method for setURL
	*
	* @param path {string} see .setURL
	*/
	function setPath ($path) {
		return $this->_path=$path;
	}

	/** set the "action" for the url, (e.g. GET,POST, DELETE, etc.)
	*
	* @param action {string} HTTP Action word.
	*/
	function setAction ($action) {
		if (empty($action))
			$action = 'GET';
		$action = strtoupper($action);
		if (preg_match('/[^A-Z]/',$action))
			throw new Service_Netflix_OAuth_Exception('Invalid action specified for Service_Netflix_OAuth.setAction');
		$this->_action = $action;
		return $this;
	}

	/** set the signatures (as well as validate the ones you have)
	*
	* @param signatures {object} object/hash of the token/signature pairs {api_key:, shared_secret:, oauth_token: oauth_secret:}
	*/
	function setTokensAndSecrets ($signatures) {
		if (!empty($signatures) && !is_array($signatures))
			throw new Service_Netflix_OAuth_Exception('Must pass dictionary array to Service_Netflix_OAuth.setTokensAndSecrets');
		if (!empty($signatures))
			foreach ($signatures as $sig=>$value)
				$this->_secrets[$sig] = $value;
		// Aliases
		if (isset($this->_secrets['api_key']))
			$this->_secrets['consumer_key'] = $this->_secrets['api_key'];
		if (isset($this->_secrets['access_token']))
			$this->_secrets['oauth_token'] = $this->_secrets['access_token'];
	if (isset($this->_secrets['access_secret']))
			$this->_secrets['oauth_secret'] = $this->_secrets['access_secret'];
		// Gauntlet
		if (empty($this->_secrets['consumer_key']))
			throw new Service_Netflix_OAuth_Exception('Missing required consumer_key in Service_Netflix_OAuth.setTokensAndSecrets');
		if (empty($this->_secrets['shared_secret']))
			throw new Service_Netflix_OAuth_Exception('Missing requires shared_secret in Service_Netflix_OAuth.setTokensAndSecrets');
		if (!empty($this->_secrets['oauth_token']) && empty($this->_secrets['oauth_secret']))
			throw new Service_Netflix_OAuth_Exception('Missing oauth_secret for supplied oauth_token in Service_Netflix_OAuth.setTokensAndSecrets');
		return $this;
	}

	/** set the signature method (currently only Plaintext or SHA-MAC1)
	*
	* @param method {string} Method of signing the transaction (only PLAINTEXT and SHA-MAC1 allowed for now)
	*/
	function setSignatureMethod ($method="") {
		if (empty($method))
			$method = $this->_default_signature_method;
		$method = strtoupper($method);
		switch($method)
		{
			case 'PLAINTEXT':
			case 'HMAC-SHA1':
				$this->_parameters['oauth_signature_method']=$method;
				break;
			default:
				throw new Service_Netflix_OAuth_Exception ('Unknown signing method specified for Service_Netflix_OAuth.setSignatureMethod');
		}
		return $this;
	}

	/** sign the request
	*
	* note: all arguments are optional, provided you've set them using the
	* other helper functions.
	*
	* @param args {object} hash of arguments for the call
	*                   {action, path, parameters (array), method, signatures (array)}
	*                   all arguments are optional.
	*/
	function sign($args=array()) {
		if (!empty($args['action']))
			$this->setAction($args['action']);
		if (!empty($args['path']))
			$this->setPath($args['path']);
		if (!empty($args['method']))
			$this->setSignatureMethod($args['method']);
		if (!empty($args['signatures']))
			$this->setTokensAndSecrets($args['signatures']);
		if (!empty($args['parameters']))
			$this->setParameters($args['parameters']);
		$normParams = $this->_normalizedParameters();
		$this->_parameters['oauth_signature'] = $this->_generateSignature($normParams);
		return Array(
			'parameters' => $this->_parameters,
			'signature' => $this->_oauthEscape($this->_parameters['oauth_signature']),
			'signed_url' => $this->_path . '?' . $this->_normalizedParameters(),
			'header' => $this->getHeaderString(),
			'sbs'=> $this->sbs
			);
	}

	/** Return a formatted "header" string
	*
	* NOTE: This doesn't set the "Authorization: " prefix, which is required.
	* I don't set it because various set header functions prefer different
	* ways to do that.
	*
	* @param args {object} see .sign
	*/
	function getHeaderString ($args=array()) {
		if (empty($this->_parameters['oauth_signature']))
			$this->sign($args);

		$result = 'OAuth ';

		foreach ($this->_parameters as $pName=>$pValue)
		{
			if (strpos($pName,'oauth_') !== 0)
				continue;
			if (is_array($pValue))
			{
				foreach ($pValue as $val)
				{
					$result .= $pName .'="' . $this->_oauthEscape($val) . '" ';
				}
			}
			else
			{
				$result .= $pName . '="' . $this->_oauthEscape($pValue) . '" ';
			}
		}
		return $result;
	}

	// Start private methods. Here be Dragons.
	// No promises are kept that any of these functions will continue to exist
	// in future versions.
	function _parseParameterString ($paramString) {
		$elements = split('&',$paramString);
		$result = array();
		foreach ($elements as $element)
		{
			list ($key,$token) = split('=',$element);
			if ($token)
				$token = urldecode($token);
			if (!empty($result[$key]))
			{
				if (!is_array($result[$key]))
					$result[$key] = array($result[$key],$token);
				else
					array_push($result[$key],$token);
			}
			else
				$result[$key]=$token;
		}
		//error_log('Parse parameters : '.print_r($result,1));
		return $result;
	}

	function _oauthEscape($string) {
		if ($string === 0)
			return 0;
		if (empty($string))
			return '';
		if (is_array($string))
			throw new Service_Netflix_OAuth_Exception('Array passed to _oauthEscape');
		$string = urlencode($string);
		$string = str_replace('+','%20',$string);
		$string = str_replace('!','%21',$string);
		$string = str_replace('*','%2A',$string);
		$string = str_replace('\'','%27',$string);
		$string = str_replace('(','%28',$string);
		$string = str_replace(')','%29',$string);
		return $string;
	}

	function _getNonce($length=5) {
		$result = '';
		$cLength = strlen($this->_nonce_chars);
		for ($i=0; $i < $length; $i++)
		{
			$rnum = rand(0,$cLength);
			$result .= substr($this->_nonce_chars,$rnum,1);
		}
		$this->_parameters['oauth_nonce'] = $result;
		return $result;
	}

	function _getApiKey() {
		if (empty($this->_secrets['consumer_key']))
		{
			throw new Service_Netflix_OAuth_Exception('No consumer_key set for Service_Netflix_OAuth');
		}
		$this->_parameters['oauth_consumer_key']=$this->_secrets['consumer_key'];
		return $this->_parameters['oauth_consumer_key'];
	}

	function _getAccessToken() {
		if (!isset($this->_secrets['oauth_secret']))
			return '';
		if (!isset($this->_secrets['oauth_token']))
			throw new Service_Netflix_OAuth_Exception('No access token (oauth_token) set for Service_Netflix_OAuth.');
		$this->_parameters['oauth_token'] = $this->_secrets['oauth_token'];
		return $this->_parameters['oauth_token'];
	}

	function _getTimeStamp() {
		return $this->_parameters['oauth_timestamp'] = time();
	}

	function _normalizedParameters() {
		$elements = array();
		$ra = 0;
		ksort($this->_parameters);
		foreach ( $this->_parameters as $paramName=>$paramValue) {
			if (preg_match('/\w+_secret/',$paramName))
				continue;
			if (is_array($paramValue))
			{
				sort($paramValue);
				foreach($paramValue as $element)
					array_push($elements,$this->_oauthEscape($paramName).'='.$this->_oauthEscape($element));
				continue;
			}
			array_push($elements,$this->_oauthEscape($paramName).'='.$this->_oauthEscape($paramValue));
		}
		return join('&',$elements);
	}

	function _generateSignature () {
		$secretKey = '';
	if(isset($this->_secrets['shared_secret']))
		$secretKey = $this->_oauthEscape($this->_secrets['shared_secret']);
	$secretKey .= '&';
	if(isset($this->_secrets['oauth_secret']))
			$secretKey .= $this->_oauthEscape($this->_secrets['oauth_secret']);
		switch($this->_parameters['oauth_signature_method'])
		{
			case 'PLAINTEXT':
				return $secretKey;

			case 'HMAC-SHA1':
				$this->sbs = $this->_oauthEscape($this->_action).'&'.$this->_oauthEscape($this->_path).'&'.$this->_oauthEscape($this->_normalizedParameters());
				//error_log('SBS: '.$sigString);
				return base64_encode(hash_hmac('sha1',$this->sbs,$secretKey,true));

			default:
				throw new Service_Netflix_OAuth_Exception('Unknown signature method for Service_Netflix_OAuth');
		}
	}
}
