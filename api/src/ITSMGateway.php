<?php

class ITSMGateway
{
    private PDO $conn;

    public function __construct(Database $rowbase)
    {
        $this->conn = $rowbase->getConnection();
    }

    public function getAll(string $id, string $entity): array
    {
        $MainTableName = $this->getITSMTableName($entity);
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
        $MainTableName = $this->getITSMTableName($entity);
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
        $MainTableName = $this->getITSMTableName($entity);
        $sql = "SELECT *
            FROM $MainTableName
            WHERE ID = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return false; // Record not found
        }

        // Check if the entity is 2 and fetch related form field values
        if ($entity === "2") {
            // Assuming that $row['ID'] is the ITSM ID and that $entity is the ITSMTypeID
            $FieldsArray = $this->getRequestFormFieldValues($entity, $row['ID']);

            // Add the form field values to the main record
            $row['FormFields'] = $FieldsArray;
        }

        return $row;
    }

    public function update(array $current, array $new, string $entity): int
    {
        $MainTableName = $this->getITSMTableName($entity);
        $setColumns = [];

        // Construct the SQL query with placeholders
        foreach ($new as $key => $value) {
            $setColumns[] = "$key = :$key";
        }

        $sql = "UPDATE $MainTableName
            SET " . implode(", ", $setColumns) . "
            WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        // Bind the new values to the statement
        foreach ($new as $key => $value) {
            $stmt->bindValue(":" . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        // Bind the ID from the current row
        $stmt->bindValue(":ID", $current["ID"], PDO::PARAM_INT);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            // Handle error appropriately
            throw new Exception("Failed to update ITSM element: " . $e->getMessage());
        }

        return $stmt->rowCount();
    }


    public function delete(string $id, string $entity): int
    {
        $MainTableName = $this->getITSMTableName($entity);
        $sql = "DELETE FROM $MainTableName
                WHERE ID = :ID";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":ID", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    private function getITSMTableName(string $ITSMTypeID): string
    {
        try {
            $sql = "SELECT TableName
                    FROM itsm_modules
                    WHERE ID = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $ITSMTypeID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the table name directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the table name if found, otherwise return an empty string
            return $row !== false ? $row['TableName'] : '';
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving ITSM table name: ' . $e->getMessage(), "getITSMTableName");
            return ''; // Return an empty string to indicate an error
        }
    }

    private function getITSMFormID(int $ITSMID, string $ITSMTableName): int
    {
        try {
            $formID = "";

            $sql = "SELECT RelatedFormID AS ID
                    FROM $ITSMTableName
                    WHERE ID = ?;";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $ITSMID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the ID directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the ID if found, otherwise return a default value
            return $row !== false ? (int)$row['ID'] : 0; // 0 indicates not found or error
            
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Forms ID: ' . $e->getMessage(), "getITSMFormID");
            return -1; // -1 indicates an error occurred
        }
    }

    private function getITSMFormsTableName(int $FormsID): string
    {
        try {
            $sql = "SELECT TableName
                FROM Forms
                WHERE ID = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $FormsID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the table name directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the table name if found, otherwise return an empty string
            return $row !== false ? $row['TableName'] : '';
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Forms table name: ' . $e->getMessage(), "getITSMFormsTableName");
            return ''; // Return an empty string to indicate an error
        }
    }

    private function getITSMFormsRowID(string $ITSMFormsTableName, int $ITSMID): int
    {
        try {
            $sql = "SELECT ID
                    FROM $ITSMFormsTableName
                    WHERE RelatedRequestID = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $ITSMID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the ID directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the ID if found, otherwise return a default value
            return $row !== false ? (int)$row['ID'] : 0; // 0 indicates not found or error
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Forms Row ID: ' . $e->getMessage(), "getITSMFormsRowID");
            return -1; // -1 indicates an error occurred
        }
    }

    private function getTableColumns(string $ITSMFormsTableName): array
    {
        try {
            $sql = "SHOW COLUMNS FROM $ITSMFormsTableName";

            $stmt = $this->conn->query($sql);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $columns;
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving table columns: ' . $e->getMessage(), "getTableColumns");
            return [];
        }
    }

    private function getITSMFieldValue(int $FieldID, string $FieldName, string $ITSMFormsTableName): string
    {
        try {
            $ReturnValue = "";

            $sql = "SELECT $FieldName
                    FROM $ITSMFormsTableName
                    WHERE ID = ?;";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $FieldID, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->bindColumn(1, $ReturnValue);
            $stmt->fetch();
            $stmt->closeCursor();

            return $ReturnValue;
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving ITSM Field Value: ' . $e->getMessage(), "getITSMFieldValue");
            return 'error';
        }
    }

    private function getITSMFormsFieldTypeID(int $ITSMFormID, string $FieldName): int
    {
        try {
            $sql = "SELECT FieldType
                    FROM forms_fieldslist
                    WHERE RelatedFormID = ? AND FieldName = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $ITSMFormID, PDO::PARAM_INT);
            $stmt->bindValue(2, $FieldName, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch the FieldType directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the FieldType if found, otherwise return a default value
            return $row !== false ? (int)$row['FieldType'] : 0; // 0 indicates not found or error
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Form Field Type ID: ' . $e->getMessage(), "getITSMFormsFieldTypeID");
            return -1; // -1 indicates an error occurred
        }
    }

    private function getFormsFieldLabel(int $ITSMFormID, string $FieldName): string
    {
        try {
            $sql = "SELECT FieldLabel
                FROM forms_fieldslist
                WHERE RelatedFormID = ? AND FieldName = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $ITSMFormID, PDO::PARAM_INT);
            $stmt->bindValue(2, $FieldName, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch the FieldLabel directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the FieldLabel if found, otherwise return an empty string
            return $row !== false ? $row['FieldLabel'] : '';
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Forms Field Label: ' . $e->getMessage(), "getFormsFieldLabel");
            return '';
        }
    }

    private function getFormsFieldTypeID(int $ITSMFormID, string $FieldName): int
    {
        try {
            $sql = "SELECT FieldType
                    FROM forms_fieldslist
                    WHERE RelatedFormID = ? AND FieldName = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $ITSMFormID, PDO::PARAM_INT);
            $stmt->bindValue(2, $FieldName, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch the FieldType directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the FieldType if found, otherwise return a default value
            return $row !== false ? (int)$row['FieldType'] : 0; // 0 indicates not found or error
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Forms Field Type ID: ' . $e->getMessage(), "getFormsFieldTypeID");
            return -1; // -1 indicates an error occurred
        }
    }

    private function getFormFieldID(string $FieldName, int $ITSMFormID): int
    {
        try {
            $sql = "SELECT ID
                    FROM forms_fieldslist
                    WHERE RelatedFormID = ? AND FieldName = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $ITSMFormID, PDO::PARAM_INT);
            $stmt->bindValue(2, $FieldName, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch the FieldType directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the FieldType if found, otherwise return a default value
            return $row !== false ? (int)$row['ID'] : 0; // 0 indicates not found or error
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Form Field ID: ' . $e->getMessage(), "getFormFieldID");
            return -1; // -1 indicates an error occurred
        }
    }

    // Function to process the options string
    private function translateOptions(string $optionsString, string $FieldDefaultValue): string
    {
        // Regular expression to match <option> elements
        $pattern = '/<option value="([^"]+)">([^<]+)<\/option>/';
        preg_match_all($pattern, $optionsString, $matches, PREG_SET_ORDER);

        // Array to hold the translated options
        $translatedOptions = [];

        foreach ($matches as $match) {
            $value = $match[1];
            $text = $match[2];
            // Translate the text
            $translatedText = translate($text);
            // Determine if this option should be selected
            $selected = ($value == $FieldDefaultValue) ? ' selected' : '';
            // Construct the new option element
            $translatedOptions[] = "<option value=\"$value\"$selected>$translatedText</option>";
        }

        // Combine the translated options back into a single string
        return implode('', $translatedOptions);
    }

    private function getFormFieldSelectOptions(int $FieldID): string
    {
        try {
            $sql = "SELECT SelectFieldOptions, AddEmpty, FieldDefaultValue
                FROM forms_fieldslist
                WHERE ID = ?;";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $FieldID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the row directly as an associative array
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Initialize SelectFieldOptions to ensure it's always a string
            $SelectFieldOptions = '';

            if ($row) {
                $SelectFieldOptions = $row["SelectFieldOptions"] ?? '';
                $AddEmpty = $row["AddEmpty"] ?? '0'; // Default to '0' if null
                $FieldDefaultValue = $row["FieldDefaultValue"] ?? '';

                $SelectFieldOptionsPre = "";

                if ($SelectFieldOptions !== '') {
                    if ($AddEmpty === '1') {
                        $SelectFieldOptionsPre = "<option value=\"\"></option>";
                    }
                    $SelectFieldOptions = str_replace("#", "", $SelectFieldOptions);
                    $SelectFieldOptions = $this->translateOptions($SelectFieldOptions, $FieldDefaultValue);
                    $SelectFieldOptions = $SelectFieldOptionsPre . $SelectFieldOptions;
                }
            }

            return $SelectFieldOptions;
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Form Field ID: ' . $e->getMessage(), "getFormFieldSelectOptions");
            return ''; // Return an empty string to indicate an error
        }
    }


    private function getFormFieldLookUpTable(int $FieldID): string
    {
        try {
            $sql = "SELECT LookupTable
                    FROM forms_fieldslist
                    WHERE ID = ?;";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $FieldID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the FieldType directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the LookupTable if found, otherwise return an empty string
            return $row !== false ? $row['LookupTable'] : '';
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Form LookupTable: ' . $e->getMessage(), "getFormFieldLookUpTable");
            return -1; // -1 indicates an error occurred
        }
    }

    private function getFormFieldLookUpField(int $FieldID): string
    {
        try {
            $sql = "SELECT LookupField
                FROM forms_fieldslist
                WHERE ID = ?;";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $FieldID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the row directly as an associative array
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Ensure LookupField is a string; return an empty string if not found
            return $row !== false && isset($row['LookupField']) ? (string)$row['LookupField'] : '';
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Form LookupField: ' . $e->getMessage(), "getFormFieldLookUpField");
            return ''; // Return an empty string to indicate an error
        }
    }

    private function getFormFieldLookUpFieldResultView(int $FieldID): string
    {
        try {
            $sql = "SELECT LookupFieldResultView
                    FROM forms_fieldslist
                    WHERE ID = ?;";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $FieldID, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the FieldType directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Ensure LookupFieldResultView is a string; return an empty string if not found
            return $row !== false && isset($row['LookupFieldResultView']) ? (string)$row['LookupFieldResultView'] : '';
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Form LookupFieldResultView: ' . $e->getMessage(), "getFormFieldLookUpFieldResultView");
            return ''; // Return an empty string to indicate an error
        }
    }

    private function getSelectFieldValue(string $FieldResultView, string $FieldLookUpTable, string $FieldLookupField, string $FieldValue): string
    {
        try {
            $sql = "SELECT $FieldResultView
                    FROM $FieldLookUpTable
                    WHERE $FieldLookupField = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $FieldValue, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the FieldType directly
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Return the FieldResultView if found, otherwise return an empty string
            return $row !== false && isset($row["$FieldResultView"]) ? (string)$row["$FieldResultView"] : '';
        } catch (Exception $e) {
            // Handle the exception
            $this->errorlog('Error retrieving Form FieldResultView: ' . $e->getMessage(), "getSelectFieldValue");
            return ''; // Return an empty string to indicate an error
        }
    }

    private function getRequestFormFieldValues(string $ITSMTypeID, string $ITSMID): array
    {
        // Get ITSM TableName
        $ITSMTableName = $this->getITSMTableName($ITSMTypeID);
        $ITSMFormID = $this->getITSMFormID($ITSMID, $ITSMTableName);
        $Excludes = array("ID", "RelatedRequestID");

        $ITSMFormsTableName = $this->getITSMFormsTableName($ITSMFormID);
        $FormsRowID = $this->getITSMFormsRowID($ITSMFormsTableName, $ITSMID);

        $FieldsArray = (array) null;

        $columns = $this->getTableColumns($ITSMFormsTableName);

        foreach ($columns as $row) {
            $FieldName = $row["Field"];
            if (!in_array($FieldName, $Excludes)) {
                $FieldValue = $this->getITSMFieldValue($FormsRowID, $FieldName, $ITSMFormsTableName);
                $FieldType = $this->getITSMFormsFieldTypeID($ITSMFormID, $FieldName);
                $FieldLabel = $this->getFormsFieldLabel($ITSMFormID, $FieldName);

                $FieldType = $this->getFormsFieldTypeID($ITSMFormID, $FieldName);
                $FieldID = $this->getFormFieldID($FieldName, $ITSMFormID);

                // Lets get real values for select options
                if ($FieldType == "4" && $FieldValue !== "") {
                    $FieldSelectOptions = $this->getFormFieldSelectOptions($FieldID);
                    if (!empty($FieldSelectOptions)) {
                    } else {
                        $FieldLookUpTable = $this->getFormFieldLookUpTable($FieldID);
                        $FieldLookupField = $this->getFormFieldLookUpField($FieldID);
                        $FieldResultView = $this->getFormFieldLookUpFieldResultView($FieldID);
                        $FieldValue = $this->getSelectFieldValue($FieldResultView, $FieldLookUpTable, $FieldLookupField, $FieldValue);
                    }
                }

                $FieldsArray[] = array($FieldLabel => $FieldValue);
            }
        }

        // Example data for illustration purposes:
        return $FieldsArray;
    }

    public function errorLog(string $logEntry, string $functionName): void
    {
        try {
            // Prepare the SQL statement
            $sql = "INSERT INTO errorlog (Error, FunctionCalled) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);

            // Bind parameters to the prepared statement
            $stmt->bindValue(1, $logEntry, PDO::PARAM_STR);
            $stmt->bindValue(2, $functionName, PDO::PARAM_STR);

            // Execute the statement
            $stmt->execute();
            $stmt->closeCursor(); // Close the cursor to free connection resources
        } catch (Exception $e) {
            // Handle any exceptions during logging
            error_log('Failed to log error: ' . $e->getMessage(), 0);
        }
    }

}
