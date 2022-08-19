<?php
namespace src\controllers;

use \core\Controller;
use \src\helpers\UserHelper;

class SearchController extends Controller {

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
        $search = filter_input(INPUT_GET, 'search');

        if (empty($search)) {
            $this->redirect('/');
        }

        $users = UserHelper::searchUser($search);

        $this->render('search', 
            ['loggedUser' => $this->loggedUser,
            'search' => $search,
            'users' => $users
        ]);
    }

}