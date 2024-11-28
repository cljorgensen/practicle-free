<?php

class TeamGateway
{
    private PDO $conn;

    public function __construct(Database $rowbase)
    {
        $this->conn = $rowbase->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT *
                FROM teams";

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
        $sql = "INSERT INTO teams (Teamname, ADUUID, Colour, Active, TeamLeader, Description)
            VALUES (:Teamname, :ADUUID, :Colour, :Active, :TeamLeader, :Description)";

        $stmt = $this->conn->prepare($sql);

        // Bind the values to the statement
        $stmt->bindValue(":Teamname", $row["Teamname"], PDO::PARAM_STR);
        $stmt->bindValue(":ADUUID", $row["ADUUID"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Colour", $row["Colour"], PDO::PARAM_STR);
        $stmt->bindValue(":Active", $row["Active"] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(":TeamLeader", $row["TeamLeader"] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":Description", $row["Description"] ?? null, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array | false
    {
        $sql = "SELECT *
                FROM teams
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
        $sql = "UPDATE teams
                SET Teamname = :Teamname,
                    ADUUID = :ADUUID,
                    Colour = :Colour,
                    Active = :Active,
                    TeamLeader = :TeamLeader,
                    Description = :Description
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        // Bind the new values to the statement
        foreach ($new as $key => $value) {
            $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
        }

        // Bind the ID from the current team
        $stmt->bindValue(":ID", $current["ID"], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $sql = "DELETE FROM teams
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":ID", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}
