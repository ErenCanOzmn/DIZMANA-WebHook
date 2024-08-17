<?php
class Database {
    private $MYSQL_HOST = "localhost";
    private $MYSQL_USER = 'xxxx';
    private $MYSQL_PASS = 'xxxx';
    private $MYSQL_DB = 'hook_database';
    private $CHARSET = 'utf8';
    private $COLLATION = 'utf8_general_ci';
    public $pdo = null;

    public function __construct() {
        $SQL = "mysql:host=" . $this->MYSQL_HOST . ";dbname=" . $this->MYSQL_DB . ";charset=" . $this->CHARSET;

        try {
            $this->pdo = new \PDO($SQL, $this->MYSQL_USER, $this->MYSQL_PASS);
            $this->pdo->exec("SET NAMES '" . $this->CHARSET . "' COLLATE '" . $this->COLLATION . "'");
            $this->pdo->exec("SET CHARACTER SET '" . $this->CHARSET . "'");
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            die("PDO ile veritabanına ulaşılamadı: " . $e->getMessage());
        }
    }

    public function saveVisitor($method, $path, $query_string, $referrer, $headers, $body, $ip_address) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO visitors (method, path, query_string, referrer, headers, body, ip_address) VALUES (:method, :path, :query_string, :referrer, :headers, :body, :ip_address)");
            $stmt->execute([
                'method' => $method,
                'path' => $path,
                'query_string' => $query_string,
                'referrer' => $referrer,
                'headers' => $headers,
                'body' => $body,
                'ip_address' => $ip_address
            ]);
        } catch (\PDOException $e) {
            die("Veri kaydedilirken bir hata oluştu: " . $e->getMessage());
        }
    }
}

$db = new Database();

// Gelen isteği kaydet
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$query_string = $_SERVER['QUERY_STRING'];
$referrer = isset($_GET['referrer']) && !empty($_GET['referrer']) 
    ? $_GET['referrer'] 
    : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'No referrer');

$headers = "";
foreach (getallheaders() as $name => $value) {
    $headers .= "$name: $value\n";
}

$body = file_get_contents('php://input');
$ip_address = $_SERVER['REMOTE_ADDR'];

$db->saveVisitor($method, $path, $query_string, $referrer, $headers, $body, $ip_address);

// İşlem tamamlandıktan sonra bir sayfaya yönlendirebilirsiniz
header("Location: eyw_hacı");
exit;
