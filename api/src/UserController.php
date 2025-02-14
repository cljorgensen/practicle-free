<?php

class UserController
{
    public function __construct(private UserGateway $gateway)
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
        $user = $this->gateway->get($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(["message" => "User not found"], JSON_UNESCAPED_UNICODE);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($user, JSON_UNESCAPED_UNICODE);
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

                $rows = $this->gateway->update($user, $data);

                echo json_encode([
                    "message" => "User $id updated",
                    "rows" => $rows
                ], JSON_UNESCAPED_UNICODE);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);

                echo json_encode([
                    "message" => "User $id deleted",
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
                    "message" => "User created",
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

        if ($is_new && empty($data["Firstname"])) {
            $errors[] = "Firstname is required";
        }

        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "Size must be an integer";
            }
        }

        return $errors;
    }
}
