<?php defined('App::NAME') OR die('You cannot execute this script.');

class Database
{
	protected static $_instance = NULL;
	
	public static function instance(Config $config = NULL)
	{
		if (Database::$_instance === NULL)
		{
			if ( ! $config instanceof Config)
				throw new InvalidArgumentException('The database connection information was missing.');

			Database::$_instance = new Database($config);
		}
		
		return Database::$_instance;
	}

	protected $_connection = NULL;
	
	final private function __construct(Config $config)
	{
		$this->_connection = new MySQLi(
			$config->get('database', 'host'),
			$config->get('database', 'username'),
			$config->get('database', 'password'),
			$config->get('database', 'name')
		);

		if ($this->_connection->connect_error)
			throw new RuntimeException('Could not connect to the database.');
	}
	
	final private function __clone() {}

	public function prepareValue($value, $type = NULL)
	{
		// Only allow scalar values (and DateTime) in the method
		if ( ! is_scalar($value) AND ! $value instanceof DateTime)
			throw new UnexpectedValueException('You cannot insert non-scalar values into the database.');

		// Cast the value to the right type if it isn't already
		if ( ! is_null($value) AND in_array($type, array('string', 'int', 'bool', 'float', 'datetime')))
		{
			if ($type === 'datetime')
			{
				// Handle datetime values
				try
				{
					$datetime = ($value instanceof DateTime) ? $value : new DateTime($value);
					$value    = $datetime->format('Y-m-d H:i:s');
					$success  = TRUE;
				}
				catch (Exception $exception)
				{
					$success = FALSE;
				}
			}
			else
			{
				// Handle scalar values
				$success = settype($value, $type);
			}

			if ( ! $success)
				throw new RuntimeException('There was an error preparing a value before inserting in the database.');
		}

		if (is_null($value))
		{
			$value = 'NULL';
		}
		elseif (is_bool($value))
		{
			$value = $value ? 1 : 0;
		}
		elseif (is_string($value))
		{
			$value = '"'.$this->_connection->real_escape_string($value).'"';
		}

		return $value;
	}

	public function select($table, $id)
	{
		$id  = is_numeric($id) ? intval($id) : 0;
		$sql = 'SELECT * FROM `'.$table.'` WHERE id = '.$id;

		$result = $this->_connection
			->query($sql)
			->fetch_assoc();

		return $result ? $result : array();
	}

	public function selectAll($table, $where = NULL, $order_by = NULL, $limit = NULL, $offset = NULL)
	{
		// Begin building query
		$sql = 'SELECT * FROM `'.$table.'`';

		// Add WHERE conditions
		if ( ! empty($where))
		{
			$sql .= ' WHERE '.$where;
		}

		// Add ORDER BY
		if ( ! empty($order_by))
		{
			$sql .= ' ORDER BY';
			foreach ($order_by as $field => $direction)
			{
				$sql .= ' '.$field.' '.$direction.',';
			}
			$sql = rtrim($sql, ',');
		}

		// Add LIMIT
		if (ctype_digit($limit))
		{
			$sql .= ' LIMIT ';
			if (ctype_digit($offset))
			{
				$sql .= ', ';
			}
			$sql .= $limit;
		}

		// Execute SELECT query and fetch all results
		$results = $this->_connection->query($sql);
		$rows = array();
		if ($results->num_rows > 0)
		{
			$rows = $results->fetch_all(MYSQLI_ASSOC);
		}
		$results->close();

		return $rows;
	}

	public function countAll($table, $where = NULL)
	{
		// Begin building query
		$sql = 'SELECT `id` FROM `'.$table.'`';

		// Add WHERE conditions
		if ( ! empty($where))
		{
			$sql .= ' WHERE '.$where;
		}

		// Execute SELECT query and COUNT
		$results = $this->_connection->query($sql);
		$count = $results->num_rows;
		$results->close();

		return $count;
	}

	public function insert($table, array $values)
	{
		$sql = 'INSERT INTO `'.$table.'` ('
			. implode(', ', array_keys($values)).') VALUES ('
			. implode(', ', array_values($values)).')';

		// Execute the query
		$result = $this->_connection->query($sql);

		// Get the ID
		if ($result)
		{
			$this->_properties['id'] = $this->_connection->insert_id;
		}

		// Return success/failure
		return (bool) $result;
	}

	public function update($table, $id, array $values)
	{
		// Build the UPDATE query
		$sql = 'UPDATE `'.$table.'` SET';
		foreach ($values as $key => $value)
		{
			$sql .= ' '.$key.' = '.$this->prepareValue($value).',';
		}
		$sql = rtrim($sql, ', ').' WHERE id = '.$id;

		// Execute the query
		$result = $this->_connection->query($sql);

		// Return success/failure
		return (bool) $result;
	}

	public function delete($table, $id)
	{
		$sql = 'DELETE FROM `'.$table.'` WHERE id = '.$this->_properties['id'];
		return (bool) $this->_connection->query($sql);
	}

	public function deleteAll($table)
	{
		$sql = 'DELETE FROM `'.$table.'`';
		return (bool) $this->_connection->query($sql);
	}
}
