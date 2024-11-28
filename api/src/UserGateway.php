<?php

class UserGateway
{
    private PDO $conn;
    
    public function __construct(Database $rowbase)
    {
        $this->conn = $rowbase->getConnection();
    }
    
    public function getAll(): array
    {
        $sql = "SELECT ID, Firstname, Lastname, CONCAT(Firstname, ' ', Lastname) AS Fullname, Email, Username, Phone, JobTitel, LastLogon, Birthday, StartDate, RelatedManager, Notes, ZoomPersRoom, RelatedUserTypeID, Active, Created_Date, CompanyID, ADUUID, LinkedIn, InactiveDate, ProfilePicture, RelatedDesignID, QRUrl, Pin, ZoomPersRoom
                FROM users";
                
        $stmt = $this->conn->query($sql);
        
        $row = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row["Active"] = (bool) $row["Active"];
            $rows[] = $row;
        }
        
        return $rows;
    }

    public function create(array $row): string
    {
        $sql = "INSERT INTO users (Firstname, Lastname, Email, Username, Created_Date, CompanyID, RelatedUserTypeID, ADUUID, JobTitel, LinkedIn, Phone, Active, InactiveDate, LastLogon, ProfilePicture, RelatedDesignID, Birthday, StartDate, RelatedManager, QRUrl, Notes, Pin, ZoomPersRoom)
            VALUES (:Firstname, :Lastname, :Email, :Username, :Created_Date, :CompanyID, :RelatedUserTypeID, :ADUUID, :JobTitel, :LinkedIn, :Phone, :Active, :InactiveDate, :LastLogon, :ProfilePicture, :RelatedDesignID, :Birthday, :StartDate, :RelatedManager, :QRUrl, :Notes, :Pin, :ZoomPersRoom)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":Firstname", $row["Firstname"], PDO::PARAM_STR);
        $stmt->bindValue(":Lastname", $row["Lastname"], PDO::PARAM_STR);
        $stmt->bindValue(":Email", $row["Email"], PDO::PARAM_STR);
        $stmt->bindValue(":Username", $row["Username"], PDO::PARAM_STR);
        $stmt->bindValue(":Created_Date", $row["Created_Date"] ?? date("Y-m-d H:i:s"), PDO::PARAM_STR);
        $stmt->bindValue(":CompanyID", $row["CompanyID"] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":RelatedUserTypeID", $row["RelatedUserTypeID"], PDO::PARAM_INT);
        $stmt->bindValue(":ADUUID", $row["ADUUID"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":JobTitel", $row["JobTitel"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":LinkedIn", $row["LinkedIn"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Phone", $row["Phone"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Active", $row["Active"] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(":InactiveDate", $row["InactiveDate"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":LastLogon", $row["LastLogon"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":ProfilePicture", $row["ProfilePicture"] ?? "default_user.png", PDO::PARAM_STR);
        $stmt->bindValue(":RelatedDesignID", $row["RelatedDesignID"] ?? 3, PDO::PARAM_INT);
        $stmt->bindValue(":Birthday", $row["Birthday"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":StartDate", $row["StartDate"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":RelatedManager", $row["RelatedManager"] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":QRUrl", $row["QRUrl"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Notes", $row["Notes"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":Pin", $row["Pin"] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":ZoomPersRoom", $row["ZoomPersRoom"] ?? null, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }


    public function get(string $id): array | false
    {
        $sql = "SELECT ID, Firstname, Lastname, CONCAT(Firstname, ' ', Lastname) AS Fullname, Email, Username, Phone, JobTitel, LastLogon, Birthday, StartDate, RelatedManager, Notes, ZoomPersRoom, RelatedUserTypeID, Active, Created_Date, CompanyID, ADUUID, LinkedIn, InactiveDate, ProfilePicture, RelatedDesignID, google_secret_code, QRUrl, Pin
            FROM users
            WHERE ID = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            $row["Active"] = (bool) $row["Active"];
            $row["Created_Date"] = $row["Created_Date"] ? date("Y-m-d H:i:s", strtotime($row["Created_Date"])) : null;
            $row["CompanyID"] = $row["CompanyID"] ? (int) $row["CompanyID"] : null;
            $row["RelatedManager"] = $row["RelatedManager"] ? (int) $row["RelatedManager"] : null;
            $row["InactiveDate"] = $row["InactiveDate"] ? date("Y-m-d H:i:s", strtotime($row["InactiveDate"])) : null;
        }

        return $row;
    }

    public function update(array $current, array $new): int
    {
        $sql = "UPDATE users
        SET Firstname = :Firstname,
            Lastname = :Lastname,
            Email = :Email,
            Username = :Username,
            Password = :Password,
            Created_Date = :Created_Date,
            CompanyID = :CompanyID,
            RelatedUserTypeID = :RelatedUserTypeID,
            ADUUID = :ADUUID,
            JobTitel = :JobTitel,
            LinkedIn = :LinkedIn,
            Phone = :Phone,
            Active = :Active,
            InactiveDate = :InactiveDate,
            LastLogon = :LastLogon,
            ProfilePicture = :ProfilePicture,
            RelatedDesignID = :RelatedDesignID,
            Birthday = :Birthday,
            StartDate = :StartDate,
            RelatedManager = :RelatedManager,
            QRUrl = :QRUrl,
            Notes = :Notes,
            Pin = :Pin,
            ZoomPersRoom = :ZoomPersRoom
        WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        // Bind the new values to the statement
        foreach ($new as $key => $value) {
            $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
        }

        // Bind the ID from the current user
        $stmt->bindValue(":ID", $current["ID"], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
    
    public function delete(string $id): int
    {
        $sql = "DELETE FROM users
                WHERE ID = :ID";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":ID", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }

}











