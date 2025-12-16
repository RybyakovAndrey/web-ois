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

    class Lectures
    {
        public static function getByCourseId($aRequest)
        {
            $courseId = intval($aRequest['courseId'] ?? 0);

            if ($courseId <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Некорректный courseId'
                ];
            }

            $lectures = Entity::getInstance()->getList(Constants::HLBLOCK_LECTURES, [
                'filter' => ['=UF_COURSE_ID' => $courseId],
                'order' => ['ID' => 'ASC']
            ]) ?? [];

            // Возвращаем результат
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ok',
                'items' => $lectures
            ]);
            exit;
        }

        public static function getById($aRequest)
        {
            $lectureId = intval($aRequest['lectureId'] ?? 0);

            if ($lectureId <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Некорректный lectureId'
                ];
            }

            $lecture = Entity::getInstance()->getList(Constants::HLBLOCK_LECTURES, [
                'filter' => ['=ID' => $lectureId],
                'limit' => 1
            ]);

            if (empty($lecture)) {
                return [
                    'status' => 'error',
                    'message' => 'Лекция не найдена'
                ];
            }

            $lecture = $lecture[0];

            // Возвращаем результат
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ok',
                'item' => $lecture
            ]);
            exit;
        }
    }
}
