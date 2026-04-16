<?php

include_once __DIR__ . "/database.php";

/**
 * Executes a given SQL query using the global database connection.
 *
 * @param string $sql The SQL query to be executed.
 * @return mysqli_result|bool The result set from the query on success, or false on failure.
 */
function query($sql) {
    global $conn;
    return mysqli_query($conn, $sql);
}


/**
 * Fetches a result row from a MySQL result set.
 *
 * @param mysqli_result $result The result resource that is being evaluated.
 * @param int $type The type of array that is to be fetched. 
 *                  1 for mysqli_fetch_array, 2 for mysqli_fetch_assoc.
 *                  Defaults to 1.
 * 
 * @return array|null Returns an array of strings that corresponds to the fetched row, 
 *                    or NULL if there are no more rows in result set.
 */
function fetch($result, $type = 1) {
    switch ($type) {
        case '1':
            $data = [];
            while ($row = mysqli_fetch_array($result)) {
                $data[] = $row;
            }
            return $data;
            break;
        case '2':
            return mysqli_fetch_assoc($result);
            break;
        
        default:
            $data = [];
            while ($row = mysqli_fetch_array($result)) {
                $data[] = $row;
            }
            return $data;
            break;
    }
}

/**
 * Retrieves all records from the specified table, optionally ordered by a specified column.
 *
 * @param string $table The name of the table to retrieve records from.
 * @param string|null $order_by Optional. The column name to order the results by. Default is null.
 * @return mysqli_result|false The result set from the query, or false on failure.
 */
function get_all($table, $order_by = null) {
    global $conn;
    $sql = "SELECT * FROM `$table`";
    if ($order_by) {
        $sql .= " ORDER BY $order_by";
    }

    return mysqli_query($conn, $sql);
}

/**
 * Retrieves a record by its ID from the specified table.
 *
 * @param string $table The name of the table to query.
 * @param int $id The ID of the record to retrieve.
 * @return mysqli_result|false The result set from the query, or false on failure.
 */
function get_by_id($table, $id) {
    global $conn;
    $id = intval($id);
    $sql = "SELECT * FROM `$table` WHERE id = $id";
    return mysqli_query($conn, $sql);
}

/**
 * Retrieves records from a specified table based on given conditions and optional ordering.
 *
 * @param string $table The name of the table to query.
 * @param array $condition An associative array representing the conditions for the WHERE clause.
 * @param string|null $order_by (Optional) The column name to order the results by.
 * @param string $order (Optional) The order direction, either 'ASC' or 'DESC'. Default is 'ASC'.
 * @return mysqli_result|false The result set from the query, or false on failure.
 */
function get_by_condition($table, $condition, $order_by = null, $order = 'ASC') {
    global $conn;
    $where = build_condition($condition);
    $sql = "SELECT * FROM `$table` WHERE $where";
    if ($order_by) {
        $sql .= " ORDER BY `$order_by` $order";
    }

    return mysqli_query($conn, $sql);
}

/**
 * Inserts a new record into the specified table.
 *
 * @param string $table The name of the table where the record will be inserted.
 * @param array $data An associative array where the keys are column names and the values are the values to be inserted.
 * @return bool|mysqli_result Returns FALSE on failure. For successful queries which produce a result set, 
 *                            such as SELECT, SHOW, DESCRIBE or EXPLAIN, mysqli_query() will return a mysqli_result object.
 *                            For other successful queries, mysqli_query() will return TRUE.
 */
function insert($table, $data) {
    global $conn;
    $columns = implode(", ", array_keys($data));
    $values = implode("', '", array_map(function ($value) use ($conn) {
        return mysqli_real_escape_string($conn, $value);
    }, array_values($data)));
    
    $sql = "INSERT INTO `$table` ($columns) VALUES ('$values')";
    return mysqli_query($conn, $sql);
}

/**
 * Updates a record in the specified table by its ID.
 *
 * @param string $table The name of the table to update.
 * @param int $id The ID of the record to update.
 * @param array $data An associative array of column-value pairs to update.
 * @return bool Returns true on success or false on failure.
 */
function update_by_id($table, $id, $data) {
    global $conn;
    $id = intval($id);
    $set = build_set_clause($data);
    
    $sql = "UPDATE `$table` SET $set WHERE id = $id";
    return mysqli_query($conn, $sql);
}

/**
 * Updates records in a specified table based on given conditions.
 *
 * @param string $table The name of the table to update.
 * @param array $data An associative array of column-value pairs to update.
 * @param array $condition An associative array of column-value pairs for the WHERE clause.
 * @return bool|mysqli_result Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE, or EXPLAIN queries it will return a mysqli_result object. For other successful queries it will return TRUE.
 */
function update_by_condition($table, $data, $condition) {
    global $conn;
    $set = build_set_clause($data);
    $where = build_condition($condition);
    
    $sql = "UPDATE `$table` SET $set WHERE $where";
    return mysqli_query($conn, $sql);
}

/**
 * Deletes a record from the specified table by its ID.
 *
 * @param string $table The name of the table from which to delete the record.
 * @param int $id The ID of the record to delete.
 * @return bool|mysqli_result Returns TRUE on success or FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries it will return a mysqli_result object.
 */
function delete_by_id($table, $id) {
    global $conn;
    $id = intval($id);
    $sql = "DELETE FROM `$table` WHERE id = $id";
    return $conn->query($sql);
}

/**
 * Deletes records from a specified table based on a given condition.
 *
 * @param string $table The name of the table from which records should be deleted.
 * @param array $condition An associative array representing the condition for deletion.
 *                         The array should be in the format ['column' => 'value'].
 * @return bool|mysqli_result Returns TRUE on success or FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries it will return a mysqli_result object.
 */
function delete_by_condition($table, $condition) {
    global $conn;
    $where = build_condition($condition);
    $sql = "DELETE FROM `$table` WHERE $where";
    return $conn->query($sql);
}

/**
 * Get the number of rows in a result set.
 *
 * This function takes a MySQLi result object and returns the number of rows
 * in the result set.
 *
 * @param mysqli_result $result The MySQLi result object.
 * @return int The number of rows in the result set.
 */
function get_num_rows($result) {
    return mysqli_num_rows($result);
}

/**
 * Builds a SQL SET clause from an associative array.
 *
 * This function takes an associative array of column-value pairs and constructs
 * a SQL SET clause for use in an UPDATE statement. It ensures that the values
 * are properly escaped to prevent SQL injection.
 *
 * @param array $data An associative array where the keys are column names and the values are the corresponding values to set.
 * @return string A SQL SET clause string.
 */
function build_set_clause($data) {
    global $conn;
    $set = "";
    foreach ($data as $key => $value) {
        $set .= "`$key` = '" . mysqli_real_escape_string($conn, $value) . "', ";
    }
    return rtrim($set, ", ");
}

/**
 * Builds a SQL WHERE condition string from an associative array.
 *
 * This function takes an associative array of conditions and constructs a SQL WHERE clause.
 * Each key-value pair in the array is converted into a condition of the form `key = 'value'`.
 * The values are escaped using `mysqli_real_escape_string` to prevent SQL injection.
 *
 * @param array $condition An associative array of conditions where the key is the column name and the value is the value to match.
 * @return string A SQL WHERE clause string constructed from the given conditions.
 */
function build_condition($condition) {
    global $conn;
    $where = "";
    foreach ($condition as $key => $value) {
        $where .= "`$key` = '" . mysqli_real_escape_string($conn, $value) . "' AND ";
    }
    return rtrim($where, " AND ");
}

/**
 * Retrieves the current session user.
 *
 * This function checks if a user is logged in by verifying the presence of 'uid' in the session.
 * If a user is logged in, it fetches the user details from the database.
 *
 * @return array|null Returns an associative array of the user details if the user is found, or null if no user is logged in.
 */
function get_session_user() {
    if (isset($_SESSION['uid'])) {
        $result = get_by_id('user', $_SESSION['uid']);

        if (get_num_rows($result) > 0) {
            return fetch($result, 2);
        }
    }

    return null;
}

/**
 * Updates the session with the current user's information.
 *
 * This function retrieves the current session user and updates the session
 * variables with the user's ID, first name, last name, and full name.
 *
 * @return bool Returns true if the session user is found and session variables are updated, false otherwise.
 */
function update_session_user() {
    $user = get_session_user();

    if ($user) {
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['name'] = $user['firstname'] . ' ' . $user['lastname'];

        return true;
    }

    return false;
}

/**
 * Checks if the user is authenticated.
 *
 * This function checks if the 'uid' session variable is set and not empty.
 *
 * @return bool Returns true if the user is authenticated, false otherwise.
 */
function is_auth() {
    if (@$_SESSION['uid'] != '') {
        return true;
    }
    
    return false;
}

/**
 * Checks if the current user has administrator privileges.
 * 
 * This function verifies if the user is both authenticated and has
 * an administrator permission level (permission = 2).
 * 
 * @return bool Returns true if the user is an authenticated administrator,
 *              false otherwise.
 */
function is_admin() {
    if (is_auth() && @$_SESSION['permission'] == '2') {
        return true;
    }

    return false;
}

/**
 * Gets the total count of records in a table.
 *
 * @param string $table The name of the table to count records from.
 * @param array|null $condition Optional conditions for the count
 * @return int The total number of records
 */
function get_count($table, $condition = null) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM `$table`";
    
    if ($condition) {
        $where = build_condition($condition);
        $sql .= " WHERE $where";
    }
    
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    return (int)$data['total'];
}

/**
 * Retrieves paginated users from the database.
 *
 * @param int $offset The starting point to retrieve records
 * @param int $per_page The number of records per page
 * @param string|null $order_by Optional column to order by
 * @return mysqli_result|false The result set from the query
 */
function get_users_paginated($offset, $per_page, $order_by = 'id DESC') {
    global $conn;
    $offset = intval($offset);
    $per_page = intval($per_page);
    
    $sql = "SELECT * FROM `user`";
    if ($order_by) {
        $sql .= " ORDER BY $order_by";
    }
    $sql .= " LIMIT $offset, $per_page";
    
    return mysqli_query($conn, $sql);
}
