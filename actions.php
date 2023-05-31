<?php
    class DatabaseConnection
    {
        private static $connection;
    
        public static function getConnection()
        {
            if (self::$connection) {
                return self::$connection;
            }
    
            $configFile = 'mysql_credentials.json';
            $config = json_decode(file_get_contents($configFile), true);
    
            $servername = $config['host'];
            $username = $config['username'];
            $password = $config['password'];
            $dbname = $config['schema'];
    
            self::$connection = mysqli_connect($servername, $username, $password, $dbname);
    
            if (!self::$connection) {
                die("Failed to connect to MySQL: " . mysqli_connect_error());
            }
    
            return self::$connection;
        }
    }
    
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            GET();
            break;
        case 'PUT':
            PUT();
            break;
        case 'POST':
            POST();
            break;
        case 'DELETE':
            DELETE();
            break;
        default:
            echo "ERROR";
    }

    function GET() {
        $conn = DatabaseConnection::getConnection();

        $query = "SELECT * FROM inventory";
        $result = mysqli_query($conn, $query);
        
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        mysqli_close($conn);
        header('Content-Type: application/json');
        $data = json_encode($data);

        print_r($data);
        return $data;
    }

    function PUT() {
        $conn = DatabaseConnection::getConnection();
        $itemId = $_GET['id'];
        
        $itemName = $_GET['itemName'];
        $quantity = $_GET['quantity'];
        $url = $_GET['url'];
        
        $query = "UPDATE inventory SET item_name = ?, quantity = ?, `url` = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $itemName, $quantity, $url, $itemId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        if($success) {
            header('Location: home.php');
            exit();
        } else {
            echo 'Error updating';
            exit();
        }
    }

    function POST() {
        $conn = DatabaseConnection::getConnection();

        $itemName = $_POST['itemName'];
        $quantity = $_POST['quantity'];
        $url = $_POST['url'];
        
        $query = "INSERT INTO `inventory` (item_name, quantity, url) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sss", $itemName, $quantity, $url);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        if($success) {
            header('Location: home.php');
            exit();
        } else {
            echo 'Error adding';
            exit();
        }
    }

    function DELETE() {
        $conn = DatabaseConnection::getConnection();
        $itemId = $_GET['id'];
        $query = "DELETE FROM inventory WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $itemId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        if ($success) {
            header('Location: home.php');
            exit();
        } else {
            echo 'Error deleting item';
            exit();
        } 
    }
?>