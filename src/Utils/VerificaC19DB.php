<?php
namespace Herald\GreenPass\Utils;

class VerificaC19DB
{

    const SQLITE_DB_NAME = 'verificac19.db';

    private string $db_complete_path;

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
        if (! $this->checkUcviTable()) {
            $this->createUcviTable();
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
                      )';
        // execute the sql commands to create new table
        $this->pdo->exec($command);
    }

    private function checkUcviTable(): bool
    {
        $tables = $this->getTableList();
        if (in_array("ucvi", $tables)) {
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

    public function addRevokedUcviToUcviList($revokedUcvi)
    {
        $sql = 'INSERT OR IGNORE INTO ucvi(revokedUcvi) VALUES(:revokedUcvi)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':revokedUcvi', $revokedUcvi);
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    public function removeRevokedUcviFromUcviList($revokedUcvi)
    {
        $sql = 'DELETE FROM ucvi WHERE revokedUcvi = :revokedUcvi)';
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
}