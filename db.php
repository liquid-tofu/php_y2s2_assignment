<?php
class mysqli_compat {
    private $pdo;
    public $connect_error = null;

    public function __construct($host, $user, $pass, $db, $port = 3306) {
        try {
            $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            $this->connect_error = $e->getMessage();
        }
    }

    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql);
            return new result_compat($stmt);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function prepare($sql) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return new stmt_compat($stmt);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function real_escape_string($str) {
        return addslashes($str);
    }

    public function close() {}

    public $insert_id;
    public function __get($name) {
        if ($name === 'insert_id') {
            return $this->pdo->lastInsertId();
        }
    }
}

class result_compat {
    private $stmt;
    public function __construct($stmt) { $this->stmt = $stmt; }
    public function fetch_assoc() { return $this->stmt->fetch(PDO::FETCH_ASSOC); }
    public function fetch_row() { return $this->stmt->fetch(PDO::FETCH_NUM); }  // ← ADD THIS LINE
    public function fetch_all($mode = MYSQLI_ASSOC) { return $this->stmt->fetchAll(PDO::FETCH_ASSOC); }
    public function __get($name) {
        if ($name === 'num_rows') return $this->stmt->rowCount();
    }
}

class stmt_compat {
    private $stmt;
    public function __construct($stmt) { $this->stmt = $stmt; }
    public function bind_param($types, &...$vars) {
        foreach ($vars as $i => &$var) {
            $this->stmt->bindParam($i + 1, $var);
        }
    }
    public function execute() { return $this->stmt->execute(); }
    public function get_result() { return new result_compat($this->stmt); }
    public function close() {}
}

$conn = new mysqli_compat(
    'crossover.proxy.rlwy.net',
    'root',
    'HsSaqkvbwlevrKyGuavIOVumwczGiAnY',
    'railway',
    28023
);

if($conn->connect_error){
    die("Connection Error: " . $conn->connect_error);
}
?>