<?php

class CompanyController
{
    public function __construct(private CompanyGateway $gateway)
    {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void
    {
        $company = $this->gateway->get($id);

        if (!$company) {
            http_response_code(404);
            echo json_encode(["message" => "Company not found"], JSON_UNESCAPED_UNICODE);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($company, JSON_UNESCAPED_UNICODE);
                break;

            case "PUT":
            case "PATCH": // Add PATCH method handling
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data, false);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors], JSON_UNESCAPED_UNICODE);
                    break;
                }

                $rows = $this->gateway->update($company, $data);

                echo json_encode([
                    "message" => "Company $id updated",
                    "rows" => $rows
                ], JSON_UNESCAPED_UNICODE);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);

                echo json_encode([
                    "message" => "Company $id deleted",
                    "rows" => $rows
                ], JSON_UNESCAPED_UNICODE);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PUT, PATCH, DELETE"); // Update the allowed methods
                break;
        }
    }

    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll(), JSON_UNESCAPED_UNICODE);
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors], JSON_UNESCAPED_UNICODE);
                    break;
                }

                $id = $this->gateway->create($data);

                http_response_code(201);
                echo json_encode([
                    "message" => "Company created",
                    "id" => $id
                ], JSON_UNESCAPED_UNICODE);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
                break;
        }
    }


    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data["CompanyName"])) {
            $errors[] = "CompanyName is required";
        }

        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "Size must be an integer";
            }
        }

        return $errors;
    }
}
