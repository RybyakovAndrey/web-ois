<?php

namespace Legacy\API;

use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;

class Users
{
    public static function get()
    {
        $users = [];
        $rsUsers = \CUser::GetList(
            ($by = "ID"),
            ($order = "ASC"),
            []
        );

        while ($arUser = $rsUsers->Fetch()) {
            $users[] = [
                'ID' => $arUser['ID'],
                'LOGIN' => $arUser['LOGIN'],
                'EMAIL' => $arUser['EMAIL'],
                'NAME' => $arUser['NAME'],
                'LAST_NAME' => $arUser['LAST_NAME'],
                'GROUPS' => \CUser::GetUserGroup($arUser['ID']),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($users);
    }
}