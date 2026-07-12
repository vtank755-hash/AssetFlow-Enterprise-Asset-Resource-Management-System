<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    /**
     * Get the singleton PDO instance
     * @return PDO
     */
    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Return a user-friendly error page or die with description
                die("Critical System Error: Unable to establish database connection. Details: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
