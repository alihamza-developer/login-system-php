<?php

namespace DB;

require_once("config.php");
require_once("functions.php");
class Database
{
	private $update_uid_length = 20;
	public $conn;
	public $insert_uid;

	# Constructor
	public function __construct()
	{
		$this->connect();
	}


	# Connect To Database
	private function connect()
	{
		$this->conn = @new \mysqli(DB_HOST, DB_USER, DB_PASSWORD);
		if ($this->conn->connect_error) {
			die("Connection Error " . $this->conn->connect_error);
		}
		$db_selected = $this->conn->select_db(DB_NAME);
		if (!$db_selected) {
			$db_created = $this->conn->query('CREATE DATABASE ' . DB_NAME);
			if ($db_created === TRUE) {
				$this->conn->select_db(DB_NAME);
			} else {
				die('Error Creating Database');
			}
		}
	}

	# Validation
	public function validText($data, $encodeHtml = true)
	{
		if (gettype($data) !== 'string') return $data;
		$data = trim($data);
		$data = addslashes($data);
		if ($encodeHtml) {
			$data = htmlspecialchars($data);
		}
		return $data;
	}

	# Valid Phone
	public function validPhone($data)
	{
		$data = preg_replace("/[^0-9+]/", "", $data);
		return $data;
	}

	# Valid Number
	public function validNum($data)
	{
		$data = preg_replace("/[^0-9]/", "", $data);
		return $data;
	}

	# Get Time
	public function getTime($datetime)
	{
		return date("d F, Y", strtotime($datetime));
	}

	# Add Backticks
	private function cover_str($data, $char)
	{
		if (gettype($data) === "array") {
			foreach ($data as $key => $value) {
				$data[$key] = "$char" . $value . "$char";
			}
			return $data;
		}
		return "$char" . $data . "$char";
	}

	# Get Fn
	public function get($type, $data = [])
	{
		if ($type === "whereQuery") {
			$conditions = arr_val($data, "condition", []);
			$limit = arr_val($data, "limit", "");
			$order_by = arr_val($data, "order_by", "");
			$limit = arr_val($data, "single_record") ? 1 : $limit;
			$condition_operator = arr_val($data, 'condition_operator', "=");
			$logical_operator = arr_val($data, 'logical_operator', "AND");
			$where = "";
			if (count($conditions) > 0) {
				$where = "WHERE ";
				foreach ($conditions as $column => $data) {
					if (gettype($data) == "array") {
						$operator = arr_val($data, "operator", $condition_operator);
						$l_operator = arr_val($data, "logical_operator", $logical_operator);
						# Value of condition
						$temp_data = $data["value"];
						if (gettype($temp_data) === "array") {
							$temp_where = '';
							foreach ($temp_data as $value) {
								$value  = $this->validText($value);
								$temp_where .= " `$column` $operator '$value' $l_operator";
							}
							$temp_where = rtrim($temp_where, $l_operator);
							$where .= " ( $temp_where ) $logical_operator";
						} else {
							$t_operator = strtolower($operator);
							if (in_array($t_operator, ['in'])) {
								$where .= " `$column` $operator $temp_data $l_operator";
							} else {
								$temp_data  = $this->validText($temp_data);
								$where .= " `$column` $operator '$temp_data' $l_operator";
							}
						}
					} else {
						$data = $this->validText($data);
						$where .= " `$column` $condition_operator '$data' $logical_operator";
					}
				}
				$where = rtrim($where, $logical_operator);
			}
			# order by
			if ($order_by) $where .= " ORDER BY $order_by";
			# Limit
			if ($limit) $where .= " LIMIT $limit";

			return $where;
		} else if ($type === "update_data_str") {
			$update_data_str = '';
			$update_data = $data['data'];
			$encodeHtml = arr_val($data, "encodeHtml", true);
			foreach ($update_data as $column => $value) {
				if (gettype($value) === "array") {
					$thisEncodeHtml = arr_val($value, "encodeHtml", $encodeHtml);
					$value = $value['value'];
					$value = $this->validText($value, $thisEncodeHtml);
					$update_data_str .= " `$column`='$value',";
				} else {
					$value = $this->validText($value, $encodeHtml);
					$update_data_str .= " `$column`='$value',";
				}
			}
			$update_data_str = rtrim($update_data_str, ",");
			return $update_data_str;
		} else if ($type === "insert_data_str") {
			$columns = [];
			$rows = [];

			$data_rows = $data['data'];
			$encodeHtml = arr_val($data, "encodeHtml", true);
			foreach ($data_rows as $record) {
				$values = [];
				foreach ($record as $key => $value) {
					$thisEncodeHtml = $encodeHtml;
					$rowData = $value;
					if (gettype($value) === "array") {
						$thisEncodeHtml = arr_val($value, "encodeHtml", $encodeHtml);
						$rowData = $value['value'];
					}

					$rowData = $this->validText($rowData, $thisEncodeHtml);
					if (!in_array($key, $columns)) $columns[] = $key;
					$values[] = $rowData;
				}
				$rows[] = $values;
			}
			$columns = $this->cover_str($columns, "`");
			$columns_str = "\n(" . implode(",", $columns) . ")";

			$rows_str = "";
			foreach ($rows as $row) {
				$row = $this->cover_str($row, "'");
				$rows_str .= "\n(" . implode(",", $row) . "),";
			}
			$rows_str = rtrim($rows_str, ",");

			return "$columns_str VALUES $rows_str;";
			return true;

			$insert_data_columns = [];
			$insert_data_values = [];
			$insert_data = $data['data'];
			$encodeHtml = arr_val($data, "encodeHtml", true);
			foreach ($insert_data as $column => $value) {
				if (gettype($value) === "array") {
					$thisEncodeHtml = arr_val($value, "encodeHtml", $encodeHtml);
					$value = $value['value'];
					$value = $this->validText($value, $thisEncodeHtml);
					$insert_data_columns[] = $column;
					$insert_data_values[] = $value;
				} else {
					$value = $this->validText($value, $encodeHtml);
					$insert_data_columns[] = $column;
					$insert_data_values[] = $value;
				}
			}
			$insert_data_columns_str = implode(',', $insert_data_columns);
			$insert_data_values_str = implode(',', $insert_data_values);

			return "($insert_data_columns_str) VALUES ($insert_data_values_str)";
		} else if ($type === "affected_rows") {
			return $this->conn->affected_rows;
		}
	}

	# Execute Select Query
	public function query($query, $options = [])
	{
		$select_query = arr_val($options, 'select_query');
		$data = $this->conn->query($query);
		if (!$select_query) return $data;
		$records = [];
		if ($data) {
			if ($data->num_rows > 0) {
				while ($row = $data->fetch_assoc()) {
					$records[] = $row;
				}
			}
		}
		return $records;
	}

	# Select Function
	public function select($table, $select_data = [], $condition = [], $options = [])
	{
		if (is_array($select_data)) {
			if (count($select_data) > 0) {
				$columns = '';
				foreach ($select_data as $column) {
					$key = strtolower($column);
					if (!strstr($column, " as ")) {
						$column = trim($column, '`');
						$columns .= "`" . $column . "`,";
					} else {
						$columns .= $column . ",";
					}
				}
				$select_data = rtrim($columns, ',');
			} else $select_data = '*';
		}

		$options['data'] = $select_data;
		$options['condition'] = $condition;

		$columns = arr_val($options, "data", "*");
		$return_query = arr_val($options, "query");
		$where_condition = $this->get("whereQuery", $options);
		$single_record = arr_val($options, "single_record");

		$query = "SELECT $columns FROM $table $where_condition";
		if ($return_query) return $query;
		$records = $this->query($query, ['select_query' => true]);
		if (!$single_record) return $records;
		if (count($records)) return $records[0];
		return false;
	}

	# Select one record
	public function select_one($table, $data = ['*'], $condition = [], $options = [])
	{
		$options['single_record'] = true;
		$record = $this->select($table, $data, $condition, $options);
		if (array_key_exists("default", $options)) {
			if ($record) return $record[$options['data']];
			return $options['default'];
		}
		if ($record) return $record;
		return $record;
	}

	# count function
	public function count($table, $condition)
	{
		$record = $this->select($table, "COUNT(1) as recordsCount", $condition, ['single_record' => true]);
		if (gettype($record) !== "array") return 0;
		return intval($record['recordsCount']);
	}

	# Update data
	public function update($table, $data = [], $condition = [], $options = [])
	{
		$return_query = arr_val($options, "query");
		$where_condition = $this->get("whereQuery", ['condition' => $condition]);
		$options['data'] = $data;
		# Update Data
		$update_data = $this->get("update_data_str", $options);

		$query = "UPDATE $table SET $update_data $where_condition";
		if ($return_query) return $query;
		$update = $this->query($query);
		return $update ? true : false;
	}

	# Delete Data
	public function delete($table, $condition = [], $data = [])
	{
		$return_query = arr_val($data, "query");
		$data['condition'] = $condition;
		$where_condition = $this->get("whereQuery", $data);
		$query = "DELETE FROM $table $where_condition";
		if ($return_query) return $query;

		$delete = $this->query($query);
		if ($delete) {
			return true;
		} else {
			return false;
		}
	}

	# Update Uid
	public function update_uid($table, $insert_id)
	{
		if (in_array($table, TABLES_WITHOUT_UID)) return false;
		$key = getRand($this->update_uid_length);
		# check if already exists
		$exists = $this->count($table, ['uid' => $key]);
		if (!$exists) {
			$update = $this->update($table, ['uid' => $key], [
				'id' => $insert_id
			]);
			if ($update)
				$this->insert_uid = $key;
			else
				$this->insert_uid = false;
		}
	}

	# Insert Data
	public function insert($table, $data = [], $options = [])
	{
		$return_query = arr_val($options, "query");
		$multiple_data = arr_val($options, "multiple");
		if (!$multiple_data) $data = [$data];
		$options['data'] = $data;
		$insert_data_str = $this->get("insert_data_str", $options);
		$query = "INSERT INTO $table $insert_data_str";
		if ($return_query) return $query;

		$insert = $this->query($query);
		if ($insert) {
			$insert_id = $this->conn->insert_id;
			$this->update_uid($table, $insert_id);
			return $insert_id;
		}
		return false;
	}

	# Save Data if not exists
	public function save($table, $data, $condition)
	{
		$saved = false;
		$exists = $this->select_one($table, 'id', $condition);
		if ($exists) {
			$saved = $this->update($table, $data, $condition);
			if (array_key_exists('id', $exists))
				$saved = $exists['id'];
		} else {
			$data = array_unique(array_merge($condition, $data));
			$saved = $this->insert($table, $data);
		}
		return $saved;
	}

	# Toggle Data - If exists delete data - Insert Data
	public function toggle($table, $condition, $data = [])
	{
		$action = false;
		$exists = $this->select_one($table, 'id', $condition);
		if ($exists) {
			$action = $this->delete($table, $condition);
		} else {
			$data = array_unique(array_merge($condition, $data));
			$action = $this->insert($table, $data);
		}
		return $action;
	}
}
$db = new Database();

require_once("functions2.php");
require_once("site-setting.php");
