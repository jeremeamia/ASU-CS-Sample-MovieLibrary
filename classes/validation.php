<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * Provides a means for validating arrays of data.
 */
class Validation
{
	/**
	 * @var array A list of keys and values to validate
	 */
	protected $_data;

	/**
	 * @var array A list of the rules for each key
	 */
	protected $_rules;

	/**
	 * Constructs a Validation object
	 *
	 * @param array $data An array of keys and values
	 */
	public function __construct(array $data)
	{
		$this->_data = $data;
		$this->_rules = array();
	}

	/**
	 * Add a rule to validate with
	 *
	 * @chainable
	 * @param string $key The key to which the rule applies
	 * @param string $rule The rule function (either in this class or PHP)
	 * @param array $args An array of arguments
	 * @return Validation
	 */
	public function addRule($key, $rule, array $args = array())
	{
		// Make sure the key exists
		if ( ! isset($this->_rules[$key]))
		{
			$this->_rules[$key] = array();
		}

		// Make sure that the rule function exists
		if ( ! function_exists($rule))
		{
			if (method_exists($this, $rule))
			{
				$rule = array($this, $rule);
			}
			else
			{
				throw new BadFunctionCallException('The validation rule "'.$rule.'"could not be mapped to an existing function.');
			}
		}

		// Add the rule
		$this->_rules[$key][] = array($rule, $args);

		return $this;
	}

	/**
	 * Add a bunch of rules at a time
	 *
	 * @chainable
	 * @param array $rule_list The list of rules to add
	 * @return Validation
	 */
	public function addRules(array $rule_list)
	{
		foreach ($rule_list as $key => $rules)
		{
			foreach ($rules as $rule => $args)
			{
				$this->addRule($key, $rule, $args);
			}
		}

		return $this;
	}

	/**
	 * Validates the data using the rules
	 *
	 * @return bool
	 */
	public function validate()
	{
		foreach ($this->_rules as $key => $rules)
		{
			foreach ($rules as $rule)
			{
				// Separate the rule function from the arguments
				list($function, $args) = $rule;

				// Add the value as the first argument
				array_unshift($args, $this->_data[$key]);

				// The function should return TRUE or else validation fails
				if ( ! call_user_func_array($function, $args))
				{
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	/**
	 * Validation: Returns TRUE if the value is not empty
	 *
	 * @param mixed $value Value to check
	 * @return bool
	 */
	public function notEmpty($value)
	{
		return (bool) ( ! empty($value));
	}

	/**
	 * Validation: Returns TRUE if the value is less than or equal to the max
	 *
	 * @param mixed $value Value to check
	 * @param int $max_length The max length allowed
	 * @return bool
	 */
	public function maxLength($value, $max_length)
	{
		return (bool) (strlen($value) <= $max_length);
	}

	/**
	 * Validation: Returns TRUE if the value is equal to the length specified
	 *
	 * @param mixed $value Value to check
	 * @param int $length The exact length required
	 * @return bool
	 */
	public function exactLength($value, $length)
	{
		return (bool) (strlen($value) == $length);
	}

	/**
	 * Validation: Returns TRUE if the value matches the regular expression
	 *
	 * @param mixed $value Value to check
	 * @param int $length The regular expression to match
	 * @return bool
	 */
	public function matchRegex($value, $regex)
	{
		return (bool) preg_match($regex, $value);
	}
}
