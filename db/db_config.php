<?php
/**
 * Mock database connection for environments without a real MySQL server.
 */
class MockStmt {
    public $affected_rows = 0;
    public $insert_id = 0;
    public $num_rows = 0;
    public $error = "";

    public function bind_param(...$args) { return true; }
    public function execute() { return true; }
    public function get_result() { return new MockResult(); }
    public function close() { return true; }
}

class MockResult {
    public $num_rows = 0;
    public function fetch_assoc() { return null; }
    public function free() { return true; }
}

class MockConn {
    public $connect_error = null;
    public $error = "";
    public $insert_id = 0;

    public function set_charset($charset) { return true; }
    public function query($sql) { return true; }
    public function multi_query($sql) { return true; }
    public function store_result() { return new MockResult(); }
    public function next_result() { return false; }
    public function prepare($sql) { return new MockStmt(); }
    public function begin_transaction() { return true; }
    public function commit() { return true; }
    public function rollback() { return true; }
    public function close() { return true; }
}

$conn = new MockConn();

// Email configuration (AWS SES)
$aws_key = "MOCK_KEY";
$aws_secret = "MOCK_SECRET";
$aws_region = "us-east-1";
$sender_email = "support@tefinitely.com";
?>
