<?php
namespace src\controllers;

use \core\Controller;
use \src\helpers\UserHelper;
use \src\helpers\PostHelper;

class HomeController extends Controller {

    private $loggedUser;

    public function __construct() 
    {
        $this->loggedUser = UserHelper::checkLogin();

        if ($this->loggedUser === false) {
            $this->redirect('/login');
        }
    }

    public function index() 
    {
        $page = intval(filter_input(INPUT_GET, 'page'));

        $feed = PostHelper::getHomeFeed($this->loggedUser->id, $page);

        $this->render('home', [
            'loggedUser' => $this->loggedUser,
            'feed' => $feed
        ]);
    }

}