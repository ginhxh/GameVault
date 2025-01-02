<?php
class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function accRender()
    {
        $user_id = 3;
        $info = $this->pdo->prepare("SELECT * FROM users WHERE user_id = :user_id;");
        $info->bindParam(":user_id", $user_id);
        $info->execute();
        $row = $info->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            extract($row); //$row['full_name']=> $full_name
            $profile_img_base64 = base64_encode($profile_img);
            $profile_img_src = 'data:image/jpeg;base64,' . $profile_img_base64;
        } else {
            throw new Exception("Something went wrong. try again.");
        }
        include("./../html/profile.php");
    }

    public function accEdit()
    {
        $user_id = 3;
        $info = $this->pdo->prepare("SELECT * FROM users WHERE user_id = :user_id;");
        $info->bindParam(":user_id", $user_id, PDO::PARAM_STR);
        $info->execute();
        $row = $info->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            extract($row);
            $profile_img_base64 = base64_encode($profile_img);
            $profile_img_src = 'data:image/jpeg;base64,' . $profile_img_base64;
        } else {
            throw new Exception("Something went wrong. try again.");
        }
        include("./../html/profile_edit.php");
    }

    public function validation()
    {
        if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['bio'])) {
            throw new Exception("Full name, email, or bio should not be empty.");
        }

        if (!preg_match("/^[a-zA-Z\s]+$/", $_POST['full_name'])) {
            throw new Exception("Full name must only contain letters and spaces.");
        }

        if (!preg_match("/^[a-zA-Z\s]+$/", $_POST['bio'])) {
            throw new Exception("Bio must only contain letters and spaces.");
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        $emails = $this->pdo->prepare("SELECT email, full_name FROM users;");
        $emails->execute();
        $rows = $emails->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if ($row["email"] == $_POST['email'] || $row["full_name"] == $_POST['full_name']) {
                throw new Exception("Email or user name already taken.");
            }
        }

        return true;
    }

    public function accModify()
    {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
            }
            $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);
        } else {
            $old_image = htmlspecialchars_decode($_POST['old_profile_image']);
            $profile_img_base64_decode = base64_decode($old_image);
            if ($profile_img_base64_decode === false) {
                throw new Exception("Error decoding old image data.");
            }
            $profile_image = $profile_img_base64_decode;
        }

        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        $user_id = $_POST['user_id'];

        $update = $this->pdo->prepare("UPDATE users SET full_name = :full_name, email = :email, profile_img = :profile_img, bio = :bio WHERE user_id = :user_id");
        $update->bindParam(":full_name", $full_name);
        $update->bindParam(":email", $email);
        $update->bindParam(":bio", $bio);
        $update->bindParam(":profile_img", $profile_image, PDO::PARAM_LOB);
        $update->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $update->execute();

        header("Location: ./../controllers/userController.php?action=on");
    }
}
