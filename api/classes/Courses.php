<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Legacy\General\RolePermissions;
use Legacy\HighLoadBlock\Entity;

class Courses
{
    public static function enrollStudent($aRequest) {
        global $USER;

        $studentId = intval($aRequest['studentId'] ?? $USER->GetID());
        $courseId = intval($aRequest['courseId'] ?? 0);

        if ($studentId <= 0 or $courseId <= 0) {
            return [
                'status' => 'error',
                'message' => 'неверный studentId или courseId'
            ];
        }

        if (!RolePermissions::isStudent($studentId)) {
            return [
                'status' => 'error',
                'message' => 'Вы не студент, поэтому не можете записаться на курс'
            ];
        }

        $exist = Entity::getInstance()->getList(Constants::HLBLOCK_ENROLL_COURSES, [
            'filter' => [
                '=UF_STUDENT_ID' => $studentId,
                '=UF_COURSE_ID' => $courseId
            ]
        ]);

        if (!empty($exist)) {
            return [
                'status' => 'error',
                'message' => 'Студент записан на курс'
            ];
        }

        try {
            $id = Entity::getInstance()->add(Constants::HLBLOCK_ENROLL_COURSES,[
                '=UF_STUDENT_ID' => $studentId,
                '=UF_COURSE_ID' => $courseId
            ]);
            
            return [
                'status' => 'success',
                'message' => 'Студент успешно записан на курс',
                'id' => $id
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

}