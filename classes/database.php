<?php defined('App::NAME') OR die('You cannot execute this script.');

class Database
{
	protected $_connection = NULL;

	public function __construct(MySQLi $connection)
	{
		$this->_connection = $connection;
	}

	public function query($sql)
	{
		$type = current(explode(' ', $sql, 2));
		$rows = $this->_connection->query($sql);

		if ($type == 'SELECT')
		{
			if ($rows)
			{
				$result = $rows->fetch_all(MYSQLI_ASSOC);
				$rows->close();
			}
			else
			{
				$result = array();
			}
		}
		else
		{
			$result = (bool) $rows;
		}

		return $result;
	}

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
		$sql = 'SELECT * FROM `'.$table.'` WHERE `id` = '.$id;

		$result = $this->_connection->query($sql);

		return $result ? $result : NULL;
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
		return $this->query($sql);
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
		return count($this->query($sql));
	}

	public function insert($table, array $values)
	{
		$sql = 'INSERT INTO `'.$table.'` ('
			. implode(', ', array_keys($values)).') VALUES ('
			. implode(', ', array_values($values)).')';

		// Execute the query and return success/failure
		return $this->query($sql);
	}

	public function lastInsertedId()
	{
		return (int) $this->_connection->insert_id;
	}

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

	public function delete($table, $id)
	{
		$sql = 'DELETE FROM `'.$table.'` WHERE `id` = '.$id;
		return $this->query($sql);
	}

	public function deleteAll($table)
	{
		$sql = 'DELETE FROM `'.$table.'`';
		return $this->query($sql);
	}
}
