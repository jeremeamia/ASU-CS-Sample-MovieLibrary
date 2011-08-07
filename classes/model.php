<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * The Model class ads ORM-type (Active Record) functionality to the App. It
 * leverages the functions of the Database class, but provides even more
 * abstraction. Models should all extend this class.
 */
abstract class Model
{
	/**
	 * @var Database The database that model data is stored in
	 */
	protected $_database;

	/**
	 * @var Config The application's configuration
	 */
	protected $_config;

	/**
	 * @var array The properties of this model
	 */
	protected $_properties = array();

	/**
	 * @var array The properties that have been changed
	 */
	protected $_changed = array();

	/**
	 * @var bool Whether or not the model has been loaded from the database
	 */
	protected $_loaded = FALSE;

	/**
	 * @var string The name of the table of which this model represents
	 */
	protected $_table = NULL;

	/**
	 * @var array The fields or columns of the database table
	 */
	protected $_fields = array();

	/**
	 * @var array The default ORDER BY information for SELECT queries
	 */
	protected $_order_by = array();

	/**
	 * Constructs a Model object
	 *
	 * @param Database $database The database to work with
	 * @param Config $config The configuration of the application
	 */
	public function __construct(Database $database, Config $config)
	{
		$this->_database = $database;
		$this->_config = $config;

		$this->_fields['id'] = 'int';

		foreach ($this->_fields as $field => $type)
		{
			$this->_properties[$field] = NULL;
		}
	}

	/**
	 * Returns whether or not the model has been loaded
	 *
	 * @return bool
	 */
	public function isLoaded()
	{
		return (bool) $this->_loaded;
	}

	/**
	 * Returns whether or not the model is a new record (not yet inserted)
	 *
	 * @return bool
	 */
	public function isNew()
	{
		return (bool) $this->_properties['id'] == NULL;
	}

	/**
	 * Returns whether or not the model is valid for saving
	 *
	 * @return bool
	 */
	public function isValid()
	{
		$validation = new Validation($this->_properties);
		$validation->addRules($this->_validationRules());
		
		return $validation->validate();
	}

	/**
	 * Saves (INSERTS or UPDATES) the model to the database
	 *
	 * @chainable
	 * @return Model
	 */
	public function save()
	{
		if ($this->isNew())
			return $this->create();
		else
			return $this->update();
	}

	/**
	 * Inserts the model into the database. The INSERT query is built from
	 * properties array.
	 *
	 * @chainable
	 * @throws RuntimeException
	 * @return Model
	 */
	public function create()
	{
		$values = array();
		foreach ($this->_properties as $key => $value)
		{
			if ($key == 'id') continue;
			$values[$key] = $this->_database->prepareValue($value, $this->_fields[$key]);
		}

		$result = $this->_database->insert($this->_table, $values);
		if ( ! $result)
			throw new RuntimeException('The '.$this->_table.' record failed to be created.');

		// Get the ID
		if ($result)
		{
			$this->_properties['id'] = $this->_database->lastInsertedId();
		}

		// Make it loaded
		$this->_loaded = TRUE;
		$this->_changed = array();

		return $this;
	}

	/**
	 * Reads a record from the database into a model
	 *
	 * @param int $id The ID of the record to be read
	 * @return Model
	 */
	public function read($id)
	{
		$results = $this->_database->select($this->_table, $id);
		$row = $results->fetch_assoc();
		$results->free();
		$row = $row ? $row : array();
		
		return $this->populate($row);
	}

	/**
	 * Reads records from the database based on the provided criteria
	 *
	 * @param string $where A string representing a SQL WHERE clause
	 * @param array $order_by An array of ORDER BY parameters
	 * @param int $limit An integer indicating the number of records to get
	 * @param int $offset And integer marking the offset of the records
	 * @return array
	 */
	public function readAll($where = NULL, array $order_by = NULL, $limit = NULL, $offset = NULL)
	{
		if (empty($order_by) AND ! empty($this->_order_by))
		{
			$order_by = $this->_order_by;
		}

		$results = $this->_database->selectAll($this->_table, $where, $order_by, $limit, $offset);

		return $this->_createModelsFromResults($results);
	}

	/**
	 * Reads the first record from the database based on the provided criteria
	 *
	 * @param string $where A string representing a SQL WHERE clause
	 * @param array $order_by An array of ORDER BY parameters
	 * @return mixed
	 */
	public function readFirst($where = NULL, array $order_by = NULL)
	{
		$results = $this->readAll($where, $order_by, 1);
		if (isset($results[0]))
			return $results[0];
		else
			return NULL;
	}

	/**
	 * Counts records from the database based on the provided criteria
	 *
	 * @param string $where A string representing a SQL WHERE clause
	 * @return
	 */
	public function countAll($where = NULL)
	{
		return $this->_database->countAll($this->_table, $where);
	}

	/**
	 * Updates the model data into the database. The INSERT query is built from
	 * the properties array.
	 *
	 * @chainable
	 * @throws RuntimeException
	 * @return Model
	 */
	public function update()
	{
		// Make sure items in changes array are unique
		$changed = array_unique($this->_changed);
		$changed = array_combine($changed, $changed);
		$changed = array_intersect_key($this->_properties, $changed);

		// Clear the changes array
		$this->_changed = array();

		$result = $this->_database->update($this->_table, $this->get('id'), $changed);
		if ( ! $result)
			throw new RuntimeException('The '.$this->_table.' record failed to be updated.');

		return $this;
	}

	/**
	 * Deletes the database record represented by this model
	 *
	 * @throws RuntimeException
	 * @return bool
	 */
	public function delete()
	{
		$result = $this->_database->delete($this->_table, $this->get('id'));
		if ( ! $result)
			throw new RuntimeException('The '.$this->_table.' record failed to be deleted.');

		return $result;
	}

	/**
	 * Retrieves the value of a property in the database
	 *
	 * @throws OutOfBoundsException
	 * @param string $key The name of the property
	 * @return array|null|string
	 */
	public function get($key)
	{
		if (array_key_exists($key, $this->_properties))
		{
			if (is_string($this->_properties[$key]))
				return stripslashes($this->_properties[$key]);
			else
				return $this->_properties[$key];
		}
		else
			throw new OutOfBoundsException('The property "'.$key.'" does not exist in table "'.$this->_table.'".');
	}

	/**
	 * Sets the value of a property in the database
	 *
	 * @throws OutOfBoundsException
	 * @param string $key The name of the property
	 * @param mixed $value The value of the property
	 * @return Model
	 */
	public function set($key, $value = NULL)
	{
		if (is_array($key))
		{
			unset($key['id']);
			$values = array_intersect_key($key, $this->_properties);
			foreach ($values as $k => $v)
			{
				$this->set($k, $v);
			}
		}
		elseif (array_key_exists($key, $this->_properties))
		{
			$this->_properties[$key] = $value;
			$this->_changed[] = $key;
		}
		else
			throw new OutOfBoundsException('The property "'.$key.'" does not exist in table "'.$this->_table.'".');

		return $this;
	}

	/**
	 * Returns the properties of the model as an array
	 *
	 * @return array
	 */
	public function asArray()
	{
		return $this->_properties;
	}

	/**
	 * Returns the properties of the model that have changed
	 *
	 * @return array
	 */
	public function changedProperties()
	{
		return $this->_changed;
	}

	/**
	 * Resets and clears out the model such that it will behave like a new
	 * instance
	 *
	 * @chainable
	 * @return Model
	 */
	public function clear()
	{
		foreach ($this->_fields as $field => $type)
		{
			$this->_properties[$field] = NULL;
		}

		$this->_loaded = FALSE;
		$this->_changed = array();

		return $this;
	}

	/**
	 * Populates data into the model
	 *
	 * @param array $values The values to assign to the properties
	 * @return Model
	 */
	protected function populate(array $values)
	{
		foreach ($values as $key => $value)
		{
			if (array_key_exists($key, $this->_properties))
			{
				$this->_properties[$key] = $value;
			}
		}

		$this->_loaded = TRUE;
		$this->_changed = array();

		return $this;
	}

	/**
	 * Helps turn database results into instances of the model
	 *
	 * @param MySQLi_Result $results The database result set
	 * @return array
	 */
	protected function _createModelsFromResults(MySQLi_Result $results)
	{
		$models = array();
		while ($row = $results->fetch_assoc())
		{
			$model = clone $this;
			$models[] = $model->populate($row);
		}
		$results->free();

		return $models;
	}

	/**
	 * Returns the validation rules for each field in the model
	 *
	 * @return array
	 */
	protected function _validationRules()
	{
		return array();
	}
}
