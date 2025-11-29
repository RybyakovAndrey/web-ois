<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Legacy\HighLoadBlock\Entity;
use Nette\Utils\DateTime;
use Legacy\General\RolePermissions;

class Submissions
{
    public static function getByTaskId($aRequest)
    {
        if (!(RolePermissions::isTeacher() or RolePermissions::isAdmin())) {
            return [
                'status' => 'error',
                'message' => 'Недостаточно прав для выполнения функции'
            ];
        }

        $id = intval($aRequest['id']);

        if ($id <= 0) {
            return [
                'status' => 'error',
                'message' => 'Некорректный id'
            ];
        }

        $params = [
            'filter' => ['=UF_TASK_ID' => $id],
            'order' => ['UF_SUBMITTED_AT' => 'ASC']
        ];

        $result = Entity::getInstance()->getList(Constants::HLBLOCK_SUBMISSIONS, $params);

        return [
            'success' => true,
            'items' => $result
        ];
    }

    public static function gradeSubmission($aRequest)
    {
        if (!(RolePermissions::isTeacher() or RolePermissions::isAdmin())) {
            return [
                'status' => 'error',
                'message' => 'Недостаточно прав для выполнения функции'
            ];
        }

        $id = intval($aRequest['id']);
        $grade = $aRequest['grade'] ?? null;
        $comment = $aRequest['comment'] ?? '';

        if ($id <= 0) {
            return [
                'status' => 'error',
                'message' => 'Некорректный id'
            ];
        }

        $params = [
            'UF_GRADED_AT' => new \Bitrix\Main\Type\DateTime(),
            'UF_COMMENT' => $comment,
        ];

        if ($grade !== null) {
            $params['UF_GRADE'] = $grade;
        }

        try {
            $success = Entity::getInstance()->update(
                Constants::HLBLOCK_SUBMISSIONS,
                $id,
                $params
            );

            if ($success) {
                return [
                    'status' => 'ok',
                    'message' => 'Работа оценена'
                ];
            }
            else {
                return [
                    'status' => 'error',
                    'message' => 'Не удалось оценить решение'
                ];
            }
        }
        catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}