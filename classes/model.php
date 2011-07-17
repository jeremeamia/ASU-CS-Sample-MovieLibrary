<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Model
{
	protected $_database;
	protected $_config;
	protected $_properties = array();
	protected $_changed    = array();
	protected $_loaded     = FALSE;
	protected $_table = NULL;
	protected $_fields     = array();
	protected $_order_by   = array();

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

	public function isLoaded()
	{
		return (bool) $this->_loaded;
	}

	public function isNew()
	{
		return (bool) $this->_properties['id'] == NULL;
	}

	public function isValid()
	{
		return TRUE;
	}

	public function save()
	{
		if ($this->isNew())
			return $this->create();
		else
			return $this->update();
	}

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

	public function read($id)
	{
		$results = $this->_database->select($this->_table, $id);
		$row = $results->fetch_assoc();
		$results->free();
		$row = $row ? $row : array();
		
		return $this->populate($row);
	}

	public function readAll($where = NULL, array $order_by = NULL, $limit = NULL, $offset = NULL)
	{
		if (empty($order_by) AND ! empty($this->_order_by))
		{
			$order_by = $this->_order_by;
		}

		$results = $this->_database->selectAll($this->_table, $where, $order_by, $limit, $offset);

		return $this->_createModelsFromResults($results);
	}

	public function readFirst($where = NULL, array $order_by = NULL, $limit = 1)
	{
		$results = $this->readAll($where, $order_by, $limit);
		if (isset($results[0]))
			return $results[0];
		else
			return NULL;
	}

	public function countAll($where = NULL)
	{
		return $this->_database->countAll($this->_table, $where);
	}

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

	public function delete()
	{
		$result = $this->_database->delete($this->_table, $this->get('id'));
		if ( ! $result)
			throw new RuntimeException('The '.$this->_table.' record failed to be deleted.');

		return $result;
	}

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

	public function asArray()
	{
		return $this->_properties;
	}

	public function changedProperties()
	{
		return $this->_changed;
	}

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
}
