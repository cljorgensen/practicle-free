<?php

class CompanyGateway
{
    private PDO $conn;

    public function __construct(Database $rowbase)
    {
        $this->conn = $rowbase->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT *
                FROM companies";

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
        $sql = "INSERT INTO companies (CompanyName, WebPage, Phone, RelatedSLAID, CustomerAccountNumber, Address, ZipCode, City, Email, CBR, Country, Notes, Active)
            VALUES (:CompanyName, :WebPage, :Phone, :RelatedSLAID, :CustomerAccountNumber, :Address, :ZipCode, :City, :Email, :CBR, :Country, :Notes, :Active)";

        $stmt = $this->conn->prepare($sql);

        // Bind the values to the statement
        $stmt->bindValue(":CompanyName", $row["CompanyName"], PDO::PARAM_STR);
        $stmt->bindValue(":WebPage", $row["WebPage"], PDO::PARAM_STR);
        $stmt->bindValue(":Phone", $row["Phone"], PDO::PARAM_STR);
        $stmt->bindValue(":RelatedSLAID", $row["RelatedSLAID"] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":CustomerAccountNumber", $row["CustomerAccountNumber"], PDO::PARAM_STR);
        $stmt->bindValue(":Address", $row["Address"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":ZipCode", $row["ZipCode"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":City", $row["City"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Email", $row["Email"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":CBR", $row["CBR"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Country", $row["Country"] ?? "DK", PDO::PARAM_STR);
        $stmt->bindValue(":Notes", $row["Notes"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Active", $row["Active"] ?? 1, PDO::PARAM_INT);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array | false
    {
        $sql = "SELECT *
                FROM companies
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
        $sql = "UPDATE companies
                SET CompanyName = :CompanyName,
                    WebPage = :WebPage,
                    Phone = :Phone,
                    RelatedSLAID = :RelatedSLAID,
                    CustomerAccountNumber = :CustomerAccountNumber,
                    Address = :Address,
                    ZipCode = :ZipCode,
                    City = :City,
                    Email = :Email,
                    CBR = :CBR,
                    Country = :Country,
                    Notes = :Notes,
                    Active = :Active
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        // Bind the new values to the statement
        foreach ($new as $key => $value) {
            $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
        }

        // Bind the ID from the current company
        $stmt->bindValue(":ID", $current["ID"], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $sql = "DELETE FROM companies
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":ID", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}
