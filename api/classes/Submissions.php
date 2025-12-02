<?php
namespace {
    header("Access-Control-Allow-Origin: http://localhost:4200");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
}
namespace Legacy\API {

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
            'status' => 'ok',
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
    public static function submitTask($aRequest)
    {

        $studentId = intval($aRequest['studentId']);

        if (!RolePermissions::isStudent($studentId)) {
            return [
                'status' => 'error',
                'message' => 'Недостаточно прав для выполнения функции'
            ];
        }

        $studentId = intval($aRequest['studentId'] ?? null);
        $taskId = intval($aRequest['taskId'] ?? 0);
        $solutionText = $aRequest['solutionText'] ?? '';

        if ($studentId <= 0 || $taskId <= 0) {
            return [
                'status' => 'error',
                'message' => 'Некорректный studentId или taskId'
            ];
        }

        try {
            $id = Entity::getInstance()->add(Constants::HLBLOCK_SUBMISSIONS, [
                'UF_STUDENT_ID' => $studentId,
                'UF_TASK_ID' => $taskId,
                'UF_SOLUTION_TEXT' => $solutionText,
                'UF_SUBMITTED_AT' => new \Bitrix\Main\Type\DateTime()
            ]);

            return [
                'status' => 'ok',
                'message' => 'Решение успешно отправлено',
                'id' => $id
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public static function getCourseSubmissionsByStudent($aRequest)
    {
        $courseId = intval($aRequest['courseId'] ?? 0);
        $studentId = intval($aRequest['studentId'] ?? 0);

        if ($courseId <= 0 || $studentId <= 0) {
            return [
                'status' => 'error',
                'message' => 'Некорректный courseId или studentId'
            ];
        }

        $tasks = Entity::getInstance()->getList(Constants::HLBLOCK_TASKS, [
            'filter' => ['=UF_COURSE_ID' => $courseId],
            'order' => ['ID' => 'ASC']
        ]) ?? [];

        if (empty($tasks)) {
            return [
                'status' => 'ok',
                'message' => 'Задания для курса отсутствуют',
                'items' => []
            ];
        }

        $taskIds = array_column($tasks, 'ID');

        $submissions = Entity::getInstance()->getList(Constants::HLBLOCK_SUBMISSIONS, [
            'filter' => [
                '=UF_STUDENT_ID' => $studentId,
                'UF_TASK_ID' => $taskIds
            ]
        ]) ?? [];

        $subsByTask = [];
        foreach ($submissions as $sub) {
            $subsByTask[$sub['UF_TASK_ID']] = $sub;
        }

        foreach ($tasks as &$task) {
            $taskId = $task['ID'];
            if (isset($subsByTask[$taskId])) {
                $sub = $subsByTask[$taskId];
                $task['submission'] = [
                    'solutionText' => $sub['UF_SOLUTION_TEXT'] ?? '',
                    'grade' => $sub['UF_GRADE'] ?? null,
                    'comment' => $sub['UF_COMMENT'] ?? '',
                    'submittedAt' => $sub['UF_SUBMITTED_AT'] ?? null,
                    'gradedAt' => $sub['UF_GRADED_AT'] ?? null
                ];
            } else {
                $task['submission'] = null;
            }
        }

        return [
            'status' => 'ok',
            'items' => $tasks
        ];

    }

}
}