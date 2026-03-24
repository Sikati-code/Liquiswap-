<?php
class Auth {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function login($phone, $password) {
        $query = "SELECT * FROM users WHERE phone_number = :phone AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_phone'] = $user['phone_number'];
            
            // Update last login
            $update = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($update);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
            
            return true;
        }
        return false;
    }
    
    public function register($data) {
        $query = "INSERT INTO users (full_name, email, phone_number, password_hash) 
                  VALUES (:name, :email, :phone, :password)";
        $stmt = $this->db->prepare($query);
        
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $stmt->bindParam(':name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone_number']);
        $stmt->bindParam(':password', $hashed_password);
        
        if ($stmt->execute()) {
            $user_id = $this->db->lastInsertId();
            
            // Add wallets
            $this->addWallet($user_id, 'MTN', $data['mtn_phone'] ?? $data['phone_number']);
            $this->addWallet($user_id, 'ORANGE', $data['orange_phone'] ?? '');
            if (!empty($data['bank_account'])) {
                $this->addWallet($user_id, 'BANK', $data['bank_account'], $data['bank_balance'] ?? 0);
            }
            
            return $user_id;
        }
        return false;
    }
    
    private function addWallet($user_id, $provider, $identifier, $balance = 0) {
        $query = "INSERT INTO wallets (user_id, provider, account_identifier, balance) 
                  VALUES (:user_id, :provider, :identifier, :balance)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':provider', $provider);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->bindParam(':balance', $balance);
        return $stmt->execute();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function getUser($user_id = null) {
        if (!$user_id && $this->isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
        }
        
        $query = "SELECT u.*, 
                         (SELECT SUM(balance) FROM wallets WHERE user_id = u.id) as total_balance
                  FROM users u 
                  WHERE u.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getUserWallets($user_id = null) {
        if (!$user_id && $this->isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
        }
        
        $query = "SELECT * FROM wallets WHERE user_id = :user_id ORDER BY is_primary DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>
