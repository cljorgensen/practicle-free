<?php

class User {
    private $dbConnection;

    // Properties corresponding to the database columns
    private $id;
    private $firstname;
    private $lastname;
    private $email;
    private $username;
    private $password;
    private $createdDate;
    private $companyId;
    private $relatedUserTypeId;
    private $adUuid;
    private $jobTitle;
    private $linkedIn;
    private $phone;
    private $active;
    private $inactiveDate;
    private $lastLogon;
    private $profilePicture;
    private $relatedDesignId;
    private $birthday;
    private $startDate;
    private $relatedManager;
    private $googleSecretCode;
    private $qrUrl;
    private $notes;
    private $pin;
    private $zoomPersonalRoom;

    // Constructor
    public function __construct($dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    // Getters and setters for each property
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    // ... (Other getters and setters for each property)

    // Save user data to the database
    public function save() {
        if ($this->id) {
            // Update existing user
            $stmt = $this->dbConnection->prepare("UPDATE users SET Firstname = ?, Lastname = ?, ... WHERE ID = ?");
            // Bind parameters and execute
            $stmt->bind_param("ssi", $this->firstname, $this->lastname, ... , $this->id);
            $stmt->execute();
        } else {
            // Insert new user
            $stmt = $this->dbConnection->prepare("INSERT INTO users (Firstname, Lastname, ...) VALUES (?, ?, ...)");
            // Bind parameters and execute
            $stmt->bind_param("ss", $this->firstname, $this->lastname, ...);
            $stmt->execute();
            $this->id = $this->dbConnection->insert_id;
        }
    }

    // Load user data from the database
    public function load($id) {
        $stmt = $this->dbConnection->prepare("SELECT * FROM users WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Set object properties
            $this->id = $row['ID'];
            $this->firstname = $row['Firstname'];
            // ... (Set other properties)
        }
    }

    // Additional methods (e.g., delete, verifyPassword, etc.)
    public function delete() {
        if ($this->id) {
            $stmt = $this->dbConnection->prepare("DELETE FROM users WHERE ID = ?");
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
        }
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    private function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // ... (Other methods as needed)
}

?>
