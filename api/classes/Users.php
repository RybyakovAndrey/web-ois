<?php

namespace Legacy\API;

use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;

class Users
{
    public static function getAll()
    {
        $result = [];

        $db = Entity::getInstance()->getList(Constants::HLBLOCK_USERS, [
            'order' => ['UF_ID_USER' => 'ASC'],
            'select' => ['UF_ID_USER', 'UF_EMAIL', 'UF_FULL_NAME', 'UF_ROLE']
        ]);

        foreach ($db as $user) {
            $result[] = $user;
        }

        return $result;
    }

    public static function getByEmail(string $email)
    {
        return Entity::getInstance()->getRow(Constants::HLBLOCK_USERS, [
            'filter' => ['UF_EMAIL' => $email]
        ]);
    }

    public static function getById(int $id)
    {
        return Entity::getInstance()->getRow(Constants::HLBLOCK_USERS, [
            'filter' => ['UF_ID_USER' => $id]
        ]);
    }
}