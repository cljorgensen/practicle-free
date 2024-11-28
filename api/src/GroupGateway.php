<?php

class GroupGateway
{
    private PDO $conn;

    public function __construct(Database $rowbase)
    {
        $this->conn = $rowbase->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT *
                FROM usergroups";

        $stmt = $this->conn->query($sql);

        $rows = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row["Active"] = (bool) $row["Active"];
            $rows[] = $row;
        }

        return $rows;
    }

    public function create(array $row): string
    {
        $sql = "INSERT INTO usergroups (GroupName, RelatedModuleID, Active, Description)
            VALUES (:GroupName, :RelatedModuleID, :Active, :Description)";

        $stmt = $this->conn->prepare($sql);

        // Bind the values to the statement
        $stmt->bindValue(":GroupName", $row["GroupName"], PDO::PARAM_STR);
        $stmt->bindValue(":RelatedModuleID", $row["RelatedModuleID"] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":Active", $row["Active"] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(":Description", $row["Description"] ?? null, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array | false
    {
        $sql = "SELECT *
                FROM usergroups
                WHERE ID = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            $row["Active"] = (bool) $row["Active"];
        }

        return $row;
    }

    public function update(array $current, array $new): int
    {
        $sql = "UPDATE usergroups
                SET GroupName = :GroupName,
                    RelatedModuleID = :RelatedModuleID,
                    Active = :Active,
                    Description = :Description
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        // Bind the new values to the statement
        foreach ($new as $key => $value) {
            $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
        }

        // Bind the ID from the current group
        $stmt->bindValue(":ID", $current["ID"], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $sql = "DELETE FROM usergroups
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":ID", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}
