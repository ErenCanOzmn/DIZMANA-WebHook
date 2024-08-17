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

    public function deleteVisitor($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM visitors WHERE id = :id");
            $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            die("Veri silinirken bir hata oluştu: " . $e->getMessage());
        }
    }

    public function getVisitors() {
        $stmt = $this->pdo->query("SELECT * FROM visitors ORDER BY id DESC");
        return $stmt->fetchAll();
    }
}

$db = new Database();

// Silme isteği var mı kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $db->deleteVisitor($_POST['delete_id']);
    header("Location: " . $_SERVER['PHP_SELF']); // Sayfayı yeniden yükle
    exit;
}

$visitors = $db->getVisitors();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Log</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 20px;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            padding: 20px;
            margin: 20px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #007BFF;
        }
        .accordion {
            margin-bottom: 10px;
            position: relative;
        }
        .accordion-button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            padding: 10px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 16px;
            transition: 0.4s;
            border-radius: 5px;
        }
        .accordion-button:hover {
            background-color: #0056b3;
        }
        .accordion-button:after {
            content: '\002B'; /* Unicode karakteri, '+' işaretini temsil eder */
            font-weight: bold;
            float: right;
        }
        .accordion-button.active:after {
            content: '\2212'; /* Unicode karakteri, '-' işaretini temsil eder */
        }
        .accordion-content {
            padding: 0 18px;
            background-color: #f9f9f9;
            display: none;
            overflow: hidden;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 5px;
        }
        .accordion-content p, .accordion-content pre {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .accordion-content p {
            background-color: #f0f0f0;
        }
        .delete-button {
            position: absolute;
            right: 10px;
            top: 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Request Log</h1>
        <?php foreach ($visitors as $visitor): ?>
            <div class="accordion">
                <button class="accordion-button">
                    ID: <?php echo $visitor->id; ?> - IP: <?php echo $visitor->ip_address; ?> - Time: <?php echo date('d/M/Y:H:i:s', strtotime($visitor->created_at)); ?>
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $visitor->id; ?>">
                    <button type="submit" class="delete-button">Sil</button>
                </form>
                <div class="accordion-content">
                    <p><strong>Method:</strong> <?php echo htmlspecialchars($visitor->method, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Path:</strong> <?php echo htmlspecialchars($visitor->path, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Query String:</strong> <?php echo htmlspecialchars($visitor->query_string, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Referrer:</strong> <?php echo htmlspecialchars($visitor->referrer, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Headers:</strong></p>
                    <pre><?php echo htmlspecialchars($visitor->headers, ENT_QUOTES, 'UTF-8'); ?></pre>
                    <p><strong>Body:</strong></p>
                    <pre><?php echo htmlspecialchars($visitor->body, ENT_QUOTES, 'UTF-8'); ?></pre>
                    <p><strong>IP Address:</strong> <?php echo htmlspecialchars($visitor->ip_address, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Time:</strong> <?php echo date('d/M/Y:H:i:s', strtotime($visitor->created_at)); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Accordion işlevselliği
        var acc = document.getElementsByClassName("accordion-button");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling.nextElementSibling; // Update to match form element
                if (panel.style.display === "block") {
                    panel.style.display = "none";
                } else {
                    panel.style.display = "block";
                }
            });
        }
    </script>
</body>
</html>
