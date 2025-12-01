<?php
namespace {  // <-- явно глобальное пространство имён
    header("Access-Control-Allow-Origin: http://localhost:4200");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
}
namespace Legacy\API {

    use Legacy\General\Constants;
    use Legacy\General\RolePermissions;
    use Legacy\HighLoadBlock\Entity;

    class Tasks
    {
        public static function getByCourseId($aRequest)
        {
            $courseId = intval($aRequest['courseId'] ?? 0);
            $userId = $aRequest['userId'] ?? 0;

            if ($courseId <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Некорректный id курса'
                ];
            }

            $tasks = Entity::getInstance()->getList(Constants::HLBLOCK_TASKS, [
                'filter' => ['=UF_COURSE_ID' => $courseId],
                'order' => ['ID' => 'ASC']
            ]) ?? [];

            $tasksWithSubmissions = [];

            if (RolePermissions::isTeacher($userId)) {
                foreach ($tasks as $task) {
                    $tasksWithSubmissions[] = [
                        'task' => $task,
                        'submission' => null
                    ];
                }
            } elseif (RolePermissions::isStudent($userId)) {
                $taskIds = array_column($tasks, 'ID');
                if (!empty($taskIds)) {
                    $submissions = Entity::getInstance()->getList(Constants::HLBLOCK_SUBMISSIONS, [
                        'filter' => [
                            '=UF_STUDENT_ID' => $userId,
                            '@UF_TASK_ID' => $taskIds
                        ]
                    ]) ?? [];

                    $submissionsMap = [];
                    foreach ($submissions as $sub) {
                        $submissionsMap[$sub['UF_TASK_ID']] = [
                            'UF_SOLUTION_TEXT' => $sub['UF_SOLUTION_TEXT'] ?? '',
                            'UF_GRADE' => $sub['UF_GRADE'] ?? null,
                            'UF_COMMENT' => $sub['UF_COMMENT'] ?? '',
                            'UF_SUBMITTED_AT' => $sub['UF_SUBMITTED_AT'] ?? null,
                            'UF_GRADED_AT' => $sub['UF_GRADED_AT'] ?? null
                        ];
                    }

                    foreach ($tasks as $task) {
                        $taskId = $task['ID'];
                        $tasksWithSubmissions[] = [
                            'task' => $task,
                            'submission' => $submissionsMap[$taskId] ?? null
                        ];
                    }
                }
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Недостаточно прав для просмотра заданий'
                ];
            }

            return [
                'status' => 'ok',
                'items' => $tasksWithSubmissions
            ];

        }
    }
}