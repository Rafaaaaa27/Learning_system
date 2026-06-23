<?php
/**
 * Base Model Class
 * Semua model extends dari class ini untuk mendapatkan fungsi CRUD dasar
 */

require_once __DIR__ . '/../config/database.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create - Insert data baru
     */
    public function create($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Read - Ambil semua data
     */
    public function getAll($conditions = [], $orderBy = null, $limit = null) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                }
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            
            if ($orderBy) {
                $sql .= " ORDER BY $orderBy";
            }
            
            if ($limit) {
                $sql .= " LIMIT $limit";
            }
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetAll Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Read - Ambil data berdasarkan ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("GetById Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Read - Ambil satu data berdasarkan kondisi
     */
    public function getOne($conditions) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                }
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            
            $sql .= " LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("GetOne Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update - Update data berdasarkan ID
     */
    public function update($id, $data) {
        try {
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            $stmt->bindValue(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete - Hapus data berdasarkan ID
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count - Hitung jumlah data
     */
    public function count($conditions = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                }
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Count Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Execute custom query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $stmt->bindValue($key + 1, $value);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute custom query (non-select)
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $stmt->bindValue($key + 1, $value);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Execute Error: " . $e->getMessage());
            return false;
        }
    }
}
?>