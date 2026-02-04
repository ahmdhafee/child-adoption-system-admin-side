<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'family_bridge_user');
define('DB_PASS', 'SecurePass123!');
define('DB_NAME', 'family_bridge_db');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Database Connection Class
 */
class Database {
    private $conn;
    private static $instance = null;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            // Check connection
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set charset to UTF-8
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            // Log error and show user-friendly message
            error_log("Database Error: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Get connection object
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Sanitize input data
     */
    public function sanitize($data) {
        return $this->conn->real_escape_string(trim($data));
    }
    
    /**
     * Execute query and return result
     */
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            
            array_unshift($values, $types);
            call_user_func_array([$stmt, 'bind_param'], $this->refValues($values));
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }
        
        return $stmt;
    }
    
    /**
     * Helper function for bind_param references
     */
    private function refValues($arr) {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }
    
    /**
     * Get last inserted ID
     */
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    // Prevent cloning and unserializing
    private function __clone() {}
    public function __wakeup() {}
}

/**
 * User Authentication Class
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Login user
     */
    public function login($email, $password, $role = 'couple') {
        try {
            // Get user by email and role
            $sql = "SELECT user_id, email, password_hash, user_role, is_active 
                    FROM users 
                    WHERE email = ? AND user_role = ? AND is_active = 1";
            
            $stmt = $this->db->query($sql, [$email, $role]);
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password_hash'])) {
                    // Password is correct
                    
                    // Reset failed login attempts
                    $this->resetFailedLogins($user['user_id']);
                    
                    // Update last login
                    $this->updateLastLogin($user['user_id']);
                    
                    // Create session
                    $this->createSession($user['user_id']);
                    
                    return [
                        'success' => true,
                        'user_id' => $user['user_id'],
                        'email' => $user['email'],
                        'role' => $user['user_role']
                    ];
                } else {
                    // Password incorrect
                    $this->recordFailedLogin($email);
                    return ['success' => false, 'message' => 'Invalid email or password'];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Register new couple
     */
    public function registerCouple($data) {
        try {
            $this->db->beginTransaction();
            
            // 1. Create user account
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email, password_hash, user_role) VALUES (?, ?, 'couple')";
            $stmt = $this->db->query($sql, [$data['email'], $password_hash]);
            $user_id = $this->db->getLastInsertId();
            
            // 2. Generate registration ID
            $registration_id = 'FB-' . date('Y') . '-' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
            
            // 3. Insert couple record
            $sql = "INSERT INTO couples (user_id, registration_id, payment_confirmation, 
                    eligibility_score, district, full_address) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->query($sql, [
                $user_id,
                $registration_id,
                $data['payment_confirmation'],
                $data['eligibility_score'],
                $data['district'],
                $data['address']
            ]);
            $couple_id = $this->db->getLastInsertId();
            
            // 4. Insert partner 1
            $sql = "INSERT INTO partners (couple_id, partner_number, full_name, age, 
                    occupation, id_number, blood_group, medical_conditions) 
                    VALUES (?, '1', ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->query($sql, [
                $couple_id,
                $data['partner1_full_name'],
                $data['partner1_age'],
                $data['partner1_occupation'],
                $data['partner1_id'],
                $data['partner1_blood_group'],
                $data['partner1_medical_conditions']
            ]);
            
            // 5. Insert partner 2
            $sql = "INSERT INTO partners (couple_id, partner_number, full_name, age, 
                    occupation, id_number, blood_group, medical_conditions) 
                    VALUES (?, '2', ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->query($sql, [
                $couple_id,
                $data['partner2_full_name'],
                $data['partner2_age'],
                $data['partner2_occupation'],
                $data['partner2_id'],
                $data['partner2_blood_group'],
                $data['partner2_medical_conditions']
            ]);
            
            // 6. Insert eligibility assessment
            $sql = "INSERT INTO eligibility_assessments (couple_id, total_score, passed_threshold,
                    partner1_age, partner2_age, combined_income_range, marriage_years, 
                    health_status, residence_years, criminal_record_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->query($sql, [
                $couple_id,
                $data['eligibility_score'],
                ($data['eligibility_score'] >= 75) ? 1 : 0,
                $data['partner1_age'],
                $data['partner2_age'],
                $data['income_range'],
                $data['marriage_years'],
                $data['health_status'],
                $data['residence_years'],
                $data['criminal_record']
            ]);
            
            // 7. Insert payment record
            $sql = "INSERT INTO payments (couple_id, payment_confirmation, amount, 
                    payment_method, payment_status) 
                    VALUES (?, ?, 250.00, ?, 'completed')";
            $stmt = $this->db->query($sql, [
                $couple_id,
                $data['payment_confirmation'],
                $data['payment_method']
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'user_id' => $user_id,
                'couple_id' => $couple_id,
                'registration_id' => $registration_id
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    public static function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
    }
    
    private function resetFailedLogins($user_id) {
        $sql = "UPDATE users SET failed_login_attempts = 0, account_locked_until = NULL 
                WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$user_id]);
    }
    
    private function updateLastLogin($user_id) {
        $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$user_id]);
    }
    
    private function createSession($user_id) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['login_time'] = time();
        $_SESSION['user_role'] = $this->getUserRoleFromId($user_id);
    }
    
    private function getUserRoleFromId($user_id) {
        $sql = "SELECT user_role FROM users WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$user_id]);
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user['user_role'];
    }
    
    private function recordFailedLogin($email) {
        $sql = "UPDATE users SET failed_login_attempts = failed_login_attempts + 1 
                WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        
        // Lock account after 5 failed attempts
        $sql = "UPDATE users SET account_locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) 
                WHERE email = ? AND failed_login_attempts >= 5";
        $stmt = $this->db->query($sql, [$email]);
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create global database instance
$database = Database::getInstance();
$auth = new Auth();
?>