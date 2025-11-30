<?php

namespace Legacy\General;

class RolePermissions
{
    public static function isTeacher($userId = null)
    {
        if (!$userId) {
            global $USER;
            $userId = $USER->GetID();
        }
        $groups = \CUser::GetUserGroup($userId);
        return in_array(RoleConstants::TEACHER_GROUP, $groups);
    }
    public static function isAdmin($userId = null)
    {
        if (!$userId) {
            global $USER;
            $userId = $USER->GetID();
        }
        $groups = \CUser::GetUserGroup($userId);
        return in_array(RoleConstants::ADMIN_GROUP, $groups);
    }
    public static function isStudent($userId = null)
    {
        if (!$userId) {
            global $USER;
            $userId = $USER->GetID();
        }
        $groups = \CUser::GetUserGroup($userId);
        return in_array(RoleConstants::STUDENT_GROUP, $groups);
    }
}