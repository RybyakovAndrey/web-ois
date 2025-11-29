<?php

namespace Legacy\API;

use Couchbase\User;
use Legacy\General\RoleConstants;

class Auth
{
    public static function login($aRequest) {
        $login = $aRequest['login'];
        $password = $aRequest['password'];

        if ($login == ''){
            echo "Логин пустой!";
        }

        global $USER;

        $authResult = $USER->Login($login, $password, 'Y');


        if ($USER->IsAuthorized()) {
            echo "Пользователь авторизован. ID: " . $USER->GetID();
        } else {
            echo "Пользователь не авторизован.";
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            echo "Сессия активна. ID сессии: " . session_id();
        } else {
            echo "Сессия не активна.";
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }


        if ($authResult['TYPE'] === 'ERROR') {
            return [
                'status' => 'error',
                'message' => $authResult['MESSAGE'],
            ];
        }

        $userId = $USER->GetID();


        $role = self::getRole($userId)['role'];

        return [
            'status' => 'ok',
            'userId' => $userId,
            'role' => $role,
            'message' => 'Авторизация прошла успешно',
        ];
    }


    public static function logout() {
        global $USER;
        $USER->Logout();
    }

    public static function getRole($userId = null) {
        $userGroups = \CUser::GetUserGroup($userId);

        $role = 'unknown';

        if (in_array(RoleConstants::STUDENT_GROUP, $userGroups)) {
            $role = 'student';
        }
        elseif (in_array(RoleConstants::TEACHER_GROUP, $userGroups)) {
            $role = 'teacher';
        }

        return [
            'status' => "ok",
            'role' => $role,
        ];
    }
}