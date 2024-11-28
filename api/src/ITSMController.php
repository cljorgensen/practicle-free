<?php

class ITSMController
{
    public function __construct(private ITSMGateway $gateway)
    {
    }

    public function processRequest(string $method, ?string $id, ?string $entity): void
    {
        if ($id) {
            $this->processResourceRequest($method, $id, $entity);
        } else {
            $this->processCollectionRequest($method, $id, $entity);
        }
    }

    private function processResourceRequest(string $method, string $id, string $entity): void
    {
        $itsm = $this->gateway->get($id, $entity);

        if (!$itsm) {
            http_response_code(404);
            echo json_encode(["message" => "ITSM not found"], JSON_UNESCAPED_UNICODE);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($itsm, JSON_UNESCAPED_UNICODE);
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

                $rows = $this->gateway->update($itsm, $data, $entity);

                echo json_encode([
                    "message" => "ITSM $id updated",
                    "rows" => $rows
                ], JSON_UNESCAPED_UNICODE);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id, $entity);

                echo json_encode([
                    "message" => "ITSM $id deleted",
                    "rows" => $rows
                ], JSON_UNESCAPED_UNICODE);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PUT, PATCH, DELETE"); // Update the allowed methods
                break;
        }
    }

    private function processCollectionRequest(string $method, string $id, string $entity): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll($id, $entity), JSON_UNESCAPED_UNICODE);
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors], JSON_UNESCAPED_UNICODE);
                    break;
                }

                $id = $this->gateway->create($data, $entity);

                http_response_code(201);
                echo json_encode([
                    "message" => "ITSM created",
                    "id" => $id
                ], JSON_UNESCAPED_UNICODE);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
                break;
        }
    }


    public function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data["ITSMname"])) {
            $errors[] = "ITSMname is required";
        }

        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "Size must be an integer";
            }
        }

        return $errors;
    }
}
