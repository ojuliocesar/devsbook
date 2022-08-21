<?php
namespace src\helpers;

use \src\models\User;
use \src\models\UserRelation;
use \src\helpers\PostHelper;

class UserHelper {
    
    public static function checkLogin() 
    {
        if (!empty($_SESSION['token'])) {
            $token = $_SESSION['token'];

            $data = User::select()->where('token', $token)->one();

            if (count($data)) {
                $loggedUser = new User();

                $loggedUser->id = $data['id'];
                $loggedUser->name = $data['name'];
                $loggedUser->email = $data['email'];
                $loggedUser->avatar = $data['avatar'];
                $loggedUser->cover = $data['cover'];

                return $loggedUser;
            }
        }
        return false;
    }

    public static function verifyLogin($email, $password)
    {
        $user = User::select()->where('email', $email)->one();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $token = md5(time() . rand(0,999));

                User::update()
                    ->set('token', $token)
                    ->where('email', $email)
                ->execute();

                return $token;
            }

        }
    }

    public static function emailExist($email)
    {
        $user = User::select()->where('email', $email)->one();
        return $user ? true : false;
    }

    public static function idExist($id)
    {
        $user = User::select()->where('id', $id)->one();

        return $user ? true : false;
    }

    public static function getUser($id, $full = false)
    {
        $data = User::select()->where('id', $id)->one();

        if ($data) {
            $user = new User();

            $user->id = $data['id'];
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->birthdate = $data['birthdate'];
            $user->city = $data['city'];
            $user->work = $data['work'];
            $user->avatar = $data['avatar'];
            $user->cover = $data['cover'];

            if ($full) {
                $user->followers = [];
                $user->following = [];
                $user->photos = [];

                // Followers
                $followers = UserRelation::select()->where('user_to', $id)->get();

                foreach($followers as $follower) {
                    $userData = User::select()->where('id', $follower['user_from'])->one();

                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->followers[] = $newUser;
                }

                // Following
                $following = UserRelation::select()->where('user_from', $id)->get();

                foreach($following as $follower) {
                    $userData = User::select()->where('id', $follower['user_to'])->one();

                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->following[] = $newUser;
                }

                // Photos
                $user->photos = PostHelper::getPhotosFrom($id);
            }

            return $user;
        }

        return false;
    }

    public static function addUser($name, $email, $password, $birthdate) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $token = md5(time() . rand(0,999));

        User::insert([
            'email' => $email,
            'password' => $hash,
            'name' => $name,
            'birthdate' => $birthdate,
            'token' => $token
        ])
        ->execute();

        return $token;
    }

    public static function isFollowing($from, $to)
    {
        $data = UserRelation::select()->where('user_from', $from)->where('user_to', $to)->one();

        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    public static function follow($from, $to)
    {
        UserRelation::insert([
            'user_from' => $from,
            'user_to' => $to
        ])->execute();
    }

    public static function unfollow($from, $to)
    {
        UserRelation::delete()->where('user_from', $from)->where('user_to', $to)->execute();

    }

    public static function searchUser($search)
    {
        $users = [];

        $data = User::select()->where('name', 'like', '%' . $search . '%')->get();

        if ($data) {
            foreach($data as $user) {
                $newUser = new User();

                $newUser->id = $user['id'];
                $newUser->name = $user['name'];
                $newUser->avatar = $user['avatar'];

                $users[] = $newUser;
            }
        }

        return $users;
    }

    public static function updateUser($userInfo, $id) {

        User::update()
            ->set('name', $userInfo['name'])
            ->set('email', $userInfo['email'])
            ->set('birthdate', $userInfo['birthdate'])
            ->set('city', $userInfo['city'])
            ->set('work', $userInfo['work'])
        ->where('id', $id)->execute();

        if (isset($userInfo['password'])) {
            User::update()
                ->set('password', password_hash($userInfo['password'], PASSWORD_DEFAULT))
            ->where('id', $id)->execute();
        }

        if (isset($userInfo['avatar'])) {
            User::update()
                ->set('avatar', $userInfo['avatar'])
            ->where('id', $id)->execute();
        }

        if (isset($userInfo['cover'])) {
            User::update()
                ->set('cover', $userInfo['cover'])
            ->where('id', $id)->execute();
        }
    }
}