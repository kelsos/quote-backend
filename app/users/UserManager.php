<?php

namespace users;

use Firebase\JWT\JWT;
use Quote\User;
use Quote\UserQuery;

use QuoteEnd\StateManager;

class UserManager
{
    public function getUser($userId)
    {
        return UserQuery::create()->findByOauthUserId($userId)->getFirst();
    }


    function createOAuthUser($userId, $username)
    {
        $random_password = password_hash(md5(uniqid(rand(), true)), PASSWORD_DEFAULT);

        $user = new User();
        $user->setUsername($username);
        $user->setOauthUserId($userId);
        $user->setPassword($random_password);
        $user->setApproved(false);
        $user->setAdmin(false);
        $user->setConfirmed(false);

        $success = $user->save() > 0;

        return $success;
    }


    function createUserToken($user)
    {
        $token = array(
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 172800,
            "id" => $user->getId()
        );

        $secret = StateManager::getInstance()->getSecret();

        $jwt = JWT::encode($token, $secret);

        return $jwt;
    }
}