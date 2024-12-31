<?php
require_once("./../models/User.php");
require_once("./../models/b.php");

class userController{
    private $action;
    private $pdo;

    public function __construct()
    {
        $this->pdo = Db::getInstance();
    }

    public function request_handler(){
        $this->action = $_POST['action'];

        switch($this->action){
            case "register":
                $this->register();
                break;
        }
    }

    private function register(){
        $user = new User($this->pdo);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $userController = new userController();
        $userController->request_handler();
    }
}