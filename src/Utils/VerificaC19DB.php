<?php
namespace Herald\GreenPass\Utils;

class VerificaC19DB
{

    const SQLITE_DB_NAME = 'verificac19.db';

    private $db_complete_path;

    /**
     * PDO instance
     *
     * @var \PDO
     */
    private $pdo;

    public function __construct()
    {
        $this->db_complete_path = FileUtils::getCacheFilePath(self::SQLITE_DB_NAME);
        $this->connect();
    }

    public function initUcvi()
    {
        if (! $this->checkUcviTable("ucvi")) {
            $this->createUcviTable();
        }
    }

    public function emptyList()
    {
        if ($this->checkUcviTable("ucvi")) {
            $sql = 'DELETE FROM ucvi';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        }
    }

    /**
     * return in instance of the PDO object that connects to the SQLite database
     */
    private function connect(): \PDO
    {
        if ($this->pdo == null) {
            $this->pdo = new \PDO("sqlite:" . $this->db_complete_path);
        }
        return $this->pdo;
    }

    private function createUcviTable()
    {
        $command = 'CREATE TABLE IF NOT EXISTS ucvi (
                        revokedUcvi VARCHAR PRIMARY KEY
                      );';
        // execute the sql commands to create new table
        $this->pdo->exec($command);
    }

    private function checkUcviTable($name): bool
    {
        $tables = $this->getTableList();
        if (in_array($name, $tables)) {
            return TRUE;
        }
        return FALSE;
    }

    private function getTableList(): array
    {
        $stmt = $this->pdo->query("SELECT name
                                   FROM sqlite_master
                                   WHERE type = 'table'
                                   ORDER BY name");
        $tables = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tables[] = $row['name'];
        }

        return $tables;
    }

    public function addRevokedUcviToUcviList(string $revokedUcvi)
    {
        $sql = 'INSERT OR IGNORE INTO ucvi(revokedUcvi) VALUES(:revokedUcvi)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':revokedUcvi', $revokedUcvi);
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    public function addAllRevokedUcviToUcviList(array $revokedUcvi)
    {
        $this->pdo->beginTransaction();
        $sql = 'INSERT OR IGNORE INTO ucvi(revokedUcvi) VALUES (?)';
        $stmt = $this->pdo->prepare($sql);

        foreach ($revokedUcvi as $d) {
            $stmt->execute([
                $d
            ]);
        }

        $this->pdo->commit();
    }

    public function removeRevokedUcviFromUcviList($revokedUcvi)
    {
        $sql = 'DELETE FROM ucvi WHERE revokedUcvi = :revokedUcvi';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':revokedUcvi', $revokedUcvi);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function getRevokedUcviList()
    {
        $stmt = $this->pdo->query('SELECT revokedUcvi FROM ucvi');
        $revokedUcvis = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $revokedUcvis[] = [
                'revokedUcvi' => $row['revokedUcvi']
            ];
        }
        return $revokedUcvis;
    }
    
    /**
     * Count number of revokedUcvi in database
     * 
     * @return int the number of revokedUcvi in database
     */
    public function countRevokedUcviInList()
    {
        $stmt = $this->pdo->query('SELECT COUNT(revokedUcvi) as conta FROM ucvi;');
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $revokedUcvis = $row['conta'];
        }
        return $revokedUcvis;
    }
    
    /**
     * Check if the revokedUcvi is in the Revoked Uvci List
     * 
     * @param string $hashedRevokedUcvi the hash of revokedUcvi to check
     * @return boolean true if in revoke list, false otherwise
     */
    public function isInRevokedUvciList(string $hashedRevokedUcvi){
        $sql = 'SELECT revokedUcvi FROM ucvi WHERE revokedUcvi = :revokedUcvi';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':revokedUcvi', $hashedRevokedUcvi);
        $stmt->execute();
        
        return ($stmt->rowCount() > 0) ? true : false;
    }
}