<?php
namespace src\helpers;

use \src\models\Post;
use \src\models\User;
use \src\models\UserRelation;

class PostHelper {
    public static function addPost($id, $type, $body)
    {
        if (!empty($id)  && !empty($body)) {
            Post::insert([
                'id_user' => $id,
                'type' => $type,
                'body' => $body
            ])->execute();
        }
    }

    public static function _postList($postList, $loggedUserId)
    {
        // 3 - transformar o resultado em objetos dos models
        $posts = [];

        foreach($postList as $postItem) {
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;

            if ($postItem['id'] === $loggedUserId) {
                $newPost->mine = true;
            }

            // 4 - preencher as informações adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();

            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];

            // 4.1 - preencher informações de LIKE
            $newPost->likeCount = 0;
            $newPost->liked = false;

            // 4.2 - preencher informações de COMMENTS
            $newPost->comments = [];

            $posts[] = $newPost;
        }

        return $posts;
    }

    public static function getUserFeed($idUser, $page, $loggedUserId)
    {
        $perPage = 2;

        // 2 - pegar os post dessa galera ordenada pela data
        $postList = Post::select()
            ->where('id_user', $idUser)
            ->orderBy('created_at', 'desc')
            ->page($page, $perPage)
        ->get();

        $total = Post::select()
            ->where('id_user', $idUser)
        ->count();

        $pageCount = ceil($total / $perPage);

        // 3 - transformar o resultado em objetos dos models
        $posts = self::_postList($postList, $loggedUserId);

        // 5 - retornar
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];
    }

    public static function getHomeFeed($id, $page)
    {
        $perPage = 3;

        // 1 - pegar a lista de usuários que eu sigo
        $userList = UserRelation::select()->where('user_from', $id)->get();

        $users = [];

        foreach($userList as $userItem) {
            $users[] = $userItem['user_to'];
        }

        $users[] = $id;

        // 2 - pegar os post dessa galera ordenada pela data
        $postList = Post::select()
            ->where('id_user', 'in', $users)
            ->orderBy('created_at', 'desc')
            ->page($page, $perPage)
        ->get();

        $total = Post::select()
            ->where('id_user', 'in', $users)
        ->count();

        $pageCount = ceil($total / $perPage);

        // 3 - transformar o resultado em objetos dos models
        $posts = self::_postList($postList, $id);

        // 5 - retornar
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];
    }

    public static function getPhotosFrom($id)
    {
        $photosData = Post::select()->where('id_user', $id)->where('type', 'photo')->get();

        $photos = [];

        foreach($photosData as $photo) {
            $newPost = new Post;
            $newPost->id = $photo['id'];
            $newPost->type = $photo['type'];
            $newPost->created_at = $photo['created_at'];
            $newPost->body = $photo['body'];

            $photos[] = $newPost;
        }

        return $photos;
    }
}