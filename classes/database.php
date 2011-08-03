<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * The Database class abstracts database operations into an easy API.
 */
class Database
{
	/**
	 * @var MySQLi The MySQL database connection
	 */
	protected $_connection = NULL;

	/**
	 * Contructs a Database object
	 *
	 * @param MySQLi $connection The MySQL database connection
	 */
	public function __construct(MySQLi $connection)
	{
		$this->_connection = $connection;
	}

	/**
	 * Executes a SQL query and returns the result. The result depends on
	 * the type of query being executed
	 *
	 * @throws RuntimeException
	 * @param string $sql A SQL query
	 * @return mixed
	 */
	public function query($sql)
	{
		// Gets the first word in the query
		$type = current(explode(' ', $sql, 2));

		// Execute the query
		$result = $this->_connection->query($sql);
		
		if ($type == 'SELECT' AND empty($result))
			throw new RuntimeException('The SQL query failed: <pre>'.$sql.'</pre>');

		return $result;
	}

	/**
	 * Prepares a value for inserting into a database query. If a type is
	 * specified, then the value will be converted to SQL-friendly version
	 * of the specified type
	 *
	 * @throws RuntimeException|UnexpectedValueException
	 * @param mixed $value A value to be inserted into the database
	 * @param string $type The type of this value
	 * @return mixed
	 */
	public function prepareValue($value, $type = NULL)
	{
		// Only allow scalar values (and DateTime) in the method
		if ( ! is_scalar($value) AND ! $value instanceof DateTime AND ! is_null($value))
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
					$value = $datetime->format('Y-m-d H:i:s');
					$success = TRUE;
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

	/**
	 * Selects a single record from a table based on ID
	 *
	 * @param string $table The name of the table
	 * @param int $id The ID of the record
	 * @return mixed
	 */
	public function select($table, $id)
	{
		$id  = is_numeric($id) ? intval($id) : 0;
		$sql = 'SELECT * FROM `'.$table.'` WHERE `id` = '.$id;

		$result = $this->query($sql);

		return $result ? $result : NULL;
	}

	/**
	 * Selects many rows from a table based on provided criteria
	 *
	 * @param string $table The name of the table
	 * @param string $where A string representing a SQL WHERE clause
	 * @param array $order_by An array of ORDER BY parameters
	 * @param int $limit An integer indicating the number of records to get
	 * @param int $offset And integer marking the offset of the records
	 * @return mixed
	 */
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
		return $this->query($sql);
	}

	/**
	 * Count the rows from a table based on provided criteria
	 *
	 * @param string $table The name of the table
	 * @param string $where A string representing a SQL WHERE clause
	 * @return
	 */
	public function countAll($table, $where = NULL)
	{
		// Begin building query
		$sql = 'SELECT `id` FROM `'.$table.'`';

		// Add WHERE conditions
		if ( ! empty($where))
		{
			$sql .= ' WHERE '.$where;
		}

		// Execute SELECT query and count the rows
		$result = $this->query($sql);
		$count = $result->num_rows;
		$result->free();
		
		return $count;
	}

	/**
	 * Inserts a record into the database
	 *
	 * @param string $table The name of the table
	 * @param array $values An array of columns and values to be inserted
	 * @return mixed
	 */
	public function insert($table, array $values)
	{
		$sql = 'INSERT INTO `'.$table.'` ('
			. implode(', ', array_keys($values)).') VALUES ('
			. implode(', ', array_values($values)).')';

		// Execute the query and return success/failure
		return $this->query($sql);
	}

	/**
	 * Get the ID of the last inserted record
	 *
	 * @return int The ID
	 */
	public function lastInsertedId()
	{
		return (int) $this->_connection->insert_id;
	}

	/**
	 * Updates an existing record identified by the ID
	 *
	 * @param string $table The name of the table
	 * @param int $id The ID of the record
	 * @param array $values An array of columns and values to be updated
	 * @return mixed
	 */
	public function update($table, $id, array $values)
	{
		// Build the UPDATE query
		$sql = 'UPDATE `'.$table.'` SET';
		foreach ($values as $key => $value)
		{
			$sql .= ' '.$key.' = '.$this->prepareValue($value).',';
		}
		$sql = rtrim($sql, ', ').' WHERE `id` = '.$id;

		// Execute the query and return success/failure
		return $this->query($sql);
	}

	/**
	 * Deletes a record identified by the ID
	 *
	 * @param string $table The name of the table
	 * @param int $id The ID of the record
	 * @return mixed
	 */
	public function delete($table, $id)
	{
		$sql = 'DELETE FROM `'.$table.'` WHERE `id` = '.$id;
		return $this->query($sql);
	}

	/**
	 * Deletes all the records in a table
	 *
	 * @param string $table The name of the table
	 * @return mixed
	 */
	public function deleteAll($table)
	{
		$sql = 'DELETE FROM `'.$table.'`';
		return $this->query($sql);
	}
}
