<?php
namespace src\controllers;

use \core\Controller;
use \src\helpers\UserHelper;
use \src\models\User;

class ConfigController extends Controller {

    private $loggedUser;

    public function __construct() 
    {
        $this->loggedUser = UserHelper::checkLogin();

        if ($this->loggedUser === false) {
            $this->redirect('/login');
        }
    }

    public function index($atts = []) 
    {
        $flash = '';

        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
        }

        $user = UserHelper::getUser($this->loggedUser->id);

        $user->birthdate = date('d/m/Y', strtotime($user->birthdate));

        $this->render('config',[
            'loggedUser' => $this->loggedUser,
            'flash' => $flash,
            'user' => $user
        ]);
    }

    public function configAction()
    {
        $user = $this->loggedUser;

        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $birthdate = filter_input(INPUT_POST, 'birthdate');
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS);
        $work = filter_input(INPUT_POST, 'work', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password');
        $passwordConfirm = filter_input(INPUT_POST, 'passwordConfirm');

        if ($name && $email && $birthdate) {

            if ($password == $passwordConfirm) {
                if (!empty($password)) {
                    $updateFields['password'] = $password;
                }
            }else {
                $_SESSION['flash'] = 'Preencha as Senhas corretamente!';
                $this->redirect('/config');
            }

            if ($user->email != $email) {
                if (UserHelper::emailExist($email)) {
                    $_SESSION['flash'] = 'Este E-mail já foi cadastrado!';
                    $this->redirect('/config');
                }
            }

            $newDate = date('Y-m-d', strtotime(str_replace('/', '-', $birthdate)));

            if (!$newDate || strtotime($newDate) === false) {
                $_SESSION['flash'] = 'Data de nascimento inválida';
                $this->redirect('/config'); 
            }

            $updateFields['name'] = $name;
            $updateFields['email'] = $email;
            $updateFields['birthdate'] = $newDate;
            $updateFields['city'] = $city;
            $updateFields['work'] = $work;

            // AVATAR
            if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
                $newAvatar = $_FILES['avatar'];

                if (in_array($newAvatar['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
                    $avatarName = $this->cutImage($newAvatar, 200, 200, 'media/avatars');
                    $updateFields['avatar'] = $avatarName;
                }

            }

            // COVER
            if (isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])) {
                $newCover = $_FILES['cover'];

                if (in_array($newCover['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
                    $coverName = $this->cutImage($newCover, 850, 310, 'media/covers');
                    $updateFields['cover'] = $coverName;
                }

            }

            UserHelper::updateUser($updateFields, $this->loggedUser->id);

        } else {
            $_SESSION['flash'] = 'Preencha as informações corretamente!';
            $this->redirect('/config');
        }


        $this->redirect('/config');
    }

    private function cutImage($file, $width, $height, $folder)
    {
        list($widthOrig, $heightOrig) = getimagesize($file['tmp_name']);

        $ratio = $widthOrig / $heightOrig;

        $newWidth = $width;
        $newHeight = $newWidth / $ratio;

        if ($newHeight < $height) {
            $newHeight = $height;

            $newWidth = $newHeight * $ratio;
        }

        $x = $width - $newWidth;
        $y = $height - $newHeight;

        $x = $x < 0 ? $x / 2 : $x;
        $y = $y < 0 ? $y / 2 : $y;

        $finalImage = imagecreatetruecolor($width, $height);

        switch($file['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
            break;

            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
            break;
        }

        imagecopyresampled(
            $finalImage, $image,
            $x, $y, 0, 0,
            $newWidth, $newHeight,
            $widthOrig, $heightOrig
        );

        $fileName = md5(uniqid(rand(), true)) . '.jpg';

        imagejpeg($finalImage, $folder . '/' . $fileName);

        return $fileName;
    }

}