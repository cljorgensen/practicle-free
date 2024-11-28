<?php 

    class Session {

        private $logged_in;

        function __construct() {
            session_start();
            $this->check_login();
        }

        // Return whether they are logged in.
        public function is_logged_in() {
            return $this->logged_in;
        }

        // When initialized, check to see if the user is logged in.
        private function check_login() {
            if (isset($_SESSION['logged_in'])) {
                // If logged in, take this action
                $this->logged_in = true;
            } else {
                // If not logged in, take this action
                $this->logged_in = false;
            }
        }

        // Set the session. User will remain logged in.
        public function login() {
            $_SESSION['logged_in'] = 1;
            $this->logged_in = true;
        }

        // Log out. Unset session and destroy it. 
        public function logout() {
            unset($_SESSION['logged_in']);
            $this->logged_in = false;
            session_destroy();
        }

        public function check_pass($pass) {
            $pass = @strip_tags($pass);
            $pass = @stripslashes($pass);

            if ($pass !== 'TheChosenPassword') return false;

            return true;
        }
    }
