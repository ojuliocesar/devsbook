<?php
namespace src\helpers;

use \src\models\Post;
use \src\models\User;
use \src\models\UserRelation;
use \src\models\PostLike;
use \src\models\PostComment;

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

            if($postItem['id_user'] == $loggedUserId) {
                $newPost->mine = true;
            }

            // 4 - preencher as informações adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();

            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];

            // 4.1 - preencher informações de LIKE
            $likes = PostLike::select()->where('id_post', $postItem['id'])->get();

            $newPost->likeCount = count($likes);
            $newPost->liked = self::isLiked($postItem['id'], $loggedUserId);

            // 4.2 - preencher informações de COMMENTS
            $newPost->comments = PostComment::select()->where('id_post', $postItem['id'])->get();
            foreach($newPost->comments as $key => $comment) {
                $newPost->comments[$key]['user'] = User::select()->where('id', $comment['id_user'])->one();
            }

            $posts[] = $newPost;
        }

        return $posts;
    }

    public static function isLiked($id, $loggedUserId)
    {
        $myLike = PostLike::select()->where('id_post', $id)->where('id_user', $loggedUserId)->get();

        if (count($myLike) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteLike($id, $loggedUserId)
    {
        PostLike::delete()->where('id_post', $id)->where('id_user', $loggedUserId)->execute();
    }

    public static function addLike($id, $loggedUserId)
    {
        PostLike::insert([
            'id_post' => $id,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H:i:s')
        ])->execute(); 
    }

    public static function addComment($id, $txt, $loggedUserId)
    {
        PostComment::insert([
            'id_post' => $id,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H:i:s'),
            'body' => $txt
        ])->execute();
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

    public static function delete($idPost, $loggedUserId)
    {
        // 1 - Verificar se o Post existe (e se ele é SEU)
        $post = Post::select()->where('id', $idPost)->where('id_user', $loggedUserId)->get();

        if (count($post) > 0) {
            $post = $post[0];

            // 2 - Deletar os likes e comments
            PostLike::delete()->where('id_post', $idPost)->execute();
            PostComment::delete()->where('id_post', $idPost)->execute();

            // 3 - Se o Post for type == photo, deletar o arquivo
            if ($post['type'] == 'photo') {
                $img = __DIR__ . '/../../public/media/uploads/' . $post['body'];

                if (file_exists($img)) {
                    unlink($img);
                }

            }

            // 4 - Deletar o Post
            Post::delete()->where('id', $idPost)->execute();
        }


    }
}