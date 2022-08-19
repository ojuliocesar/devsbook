<?php
namespace src\controllers;

use \core\Controller;
use \src\helpers\UserHelper;
use \src\helpers\PostHelper;

class ProfileController extends Controller {

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
        $page = intval(filter_input(INPUT_GET, 'page'));

        // Detectando o usuário acessado
        $id = $this->loggedUser->id;

        if (!empty($atts['id'])) {
            $id = $atts['id'];
        }

        // Pegando informações do usuário
        $user = UserHelper::getUser($id, true);

        if (!$user) {
            $this->redirect('/');
        }

        $dateFrom = new \DateTime($user->birthdate);
        $dateTo = new \DateTime('today');
        $user->ageYears = $dateFrom->diff($dateTo)->y;
        
        // Pegando o feed do usuário
        $feed = PostHelper::getUserFeed($id, $page, $this->loggedUser->id);

        // Verificar se eu sigo o usuário
        $isFollowing = false; 

        if ($user->id != $this->loggedUser->id) {
            $isFollowing = UserHelper::isFollowing($this->loggedUser->id, $user->id);
        }

        $this->render('profile', 
            ['loggedUser' => $this->loggedUser,
            'user' => $user,
            'feed' => $feed,
            'isFollowing' => $isFollowing
        ]);
    }

    public function follow($atts)
    {
        $to = intval($atts['id']);

        $exist = UserHelper::idExist($to);

        if ($exist) {
            if (UserHelper::isFollowing($this->loggedUser->id, $to)) {
                // unFollow

                UserHelper::unfollow($this->loggedUser->id, $to);
            } else {
                // Follow

                UserHelper::follow($this->loggedUser->id, $to);
            }
        }

        $this->redirect('/perfil/' . $to);


    }

    public function friends($atts = [])
    {
        // Detectando o usuário acessado
        $id = $this->loggedUser->id;

        if (!empty($atts['id'])) {
            $id = $atts['id'];
        }

        //Pegando informações do usuário
        $user = UserHelper::getUser($id, true);

        if (!$user) {
            $this->redirect('/');
        }

        $dateFrom = new \DateTime($user->birthdate);
        $dateTo = new \DateTime('today');
        $user->ageYears = $dateFrom->diff($dateTo)->y;

        // Verificar se eu sigo o usuário
        $isFollowing = false; 

        if ($user->id != $this->loggedUser->id) {
            $isFollowing = UserHelper::isFollowing($this->loggedUser->id, $user->id);
        }

        $this->render('friends', [
            'loggedUser' => $this->loggedUser,
            'user' => $user,
            'isFollowing' => $isFollowing
        ]);
    }

    public function photos($atts = [])
    {
        // Detectando o usuário acessado
        $id = $this->loggedUser->id;

        if (!empty($atts['id'])) {
            $id = $atts['id'];
        }

        //Pegando informações do usuário
        $user = UserHelper::getUser($id, true);

        if (!$user) {
            $this->redirect('/');
        }

        $dateFrom = new \DateTime($user->birthdate);
        $dateTo = new \DateTime('today');
        $user->ageYears = $dateFrom->diff($dateTo)->y;

        // Verificar se eu sigo o usuário
        $isFollowing = false; 

        if ($user->id != $this->loggedUser->id) {
            $isFollowing = UserHelper::isFollowing($this->loggedUser->id, $user->id);
        }

        $this->render('photos', [
            'loggedUser' => $this->loggedUser,
            'user' => $user,
            'isFollowing' => $isFollowing
        ]);
    }

}