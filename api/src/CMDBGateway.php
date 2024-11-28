<?php

class CMDBGateway
{
    private PDO $conn;

    public function __construct(Database $rowbase)
    {
        $this->conn = $rowbase->getConnection();
    }

    public function getAll(string $id, string $entity): array
    {
        $MainTableName = $this->getCMDBTableName($entity);
        $sql = "SELECT *
                FROM $MainTableName";

        $stmt = $this->conn->query($sql);

        $rows = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function create(array $row, string $entity): string
    {
        $MainTableName = $this->getCMDBTableName($entity);
        $columns = implode(", ", array_keys($row));
        $placeholders = implode(", :", array_keys($row));

        $sql = "INSERT INTO $MainTableName ($columns)
            VALUES (:$placeholders)";

        $stmt = $this->conn->prepare($sql);

        // Bind the values to the statement
        foreach ($row as $key => $value) {
            $stmt->bindValue(":" . $key, $value);
        }

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id, string $entity): array | false
    {
        $MainTableName = $this->getCMDBTableName($entity);
        $sql = "SELECT *
                FROM $MainTableName
                WHERE ID = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : false;
    }

    public function update(array $current, array $new, string $entity): int
    {
        $MainTableName = $this->getCMDBTableName($entity);
        $setColumns = [];

        foreach ($new as $key => $value) {
            $setColumns[] = "$key = :$key";
        }

        $sql = "UPDATE $MainTableName
                SET " . implode(", ", $setColumns) . "
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        // Bind the new values to the statement
        foreach ($new as $key => $value) {
            $stmt->bindValue(":" . $key, $value);
        }

        // Bind the ID from the current row
        $stmt->bindValue(":ID", $current["ID"], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id, string $entity): int
    {
        $MainTableName = $this->getCMDBTableName($entity);
        $sql = "DELETE FROM $MainTableName
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":ID", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    private function getCMDBTableName(string $CMDBTypeID): string
    {
        try {
            $tableName = "";

            $sql = "SELECT TableName
                    FROM cmdb_cis
                    WHERE ID = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $CMDBTypeID, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->bindColumn(1, $tableName);
            $stmt->fetch();
            $stmt->closeCursor();
            
            return $tableName;
        } catch (Exception $e) {
            // Handle the exception
            errorlog('Error retrieving CMDB table name: ' . $e->getMessage(), "getCMDBTableName");
            return 'error';
        }
    }
}
