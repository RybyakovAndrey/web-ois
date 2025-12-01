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

    class Courses
    {
        public static function enrollStudent($aRequest)
        {
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
                $id = Entity::getInstance()->add(Constants::HLBLOCK_ENROLL_COURSES, [
                    'UF_STUDENT_ID' => $studentId,
                    'UF_COURSE_ID' => $courseId
                ]);

                return [
                    'status' => 'ok',
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

        public static function getCoursesByStudentId($aRequest)
        {
            $studentId = intval($aRequest['studentId'] ?? 0);

            if ($studentId <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Некорректное значение студента'
                ];
            }

            if (!RolePermissions::isStudent($studentId)) {
                return [
                    'status' => 'error',
                    'message' => 'Вы не студент, поэтому не можете просматривать курсы'
                ];
            }

            $enrollments = Entity::getInstance()->getList(Constants::HLBLOCK_ENROLL_COURSES, [
                'filter' => ['=UF_STUDENT_ID' => $studentId]
            ]) ?? [];

            if (empty($enrollments)) {
                return [
                    'status' => 'ok',
                    'items' => [],
                    'message' => 'Студент не записался ни на один курс'
                ];
            }

            $courseIds = array_column($enrollments, 'UF_COURSE_ID');

            $courses = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES, [
                'filter' => [
                    '@ID' => $courseIds
                ],
                'order' => [
                    'ID' => 'ASC'
                ]
            ]) ?? [];

            return [
                'status' => 'ok',
                'items' => $courses,
            ];

        }

        public static function getAvailableCourseByStudentId($aRequest)
        {

            $studentId = intval($aRequest['studentId'] ?? 0);

            if ($studentId <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Некорректное значение студента'
                ];
            }

            if (!RolePermissions::isStudent($studentId)) {
                return [
                    'status' => 'error',
                    'message' => 'Вы не студент, поэтому не можете просматривать доступные курсы'
                ];
            }

            $enrollments = Entity::getInstance()->getList(Constants::HLBLOCK_ENROLL_COURSES, [
                'filter' => ['=UF_STUDENT_ID' => $studentId]
            ]);

            $enrolledCourseIds = array_column($enrollments, 'UF_COURSE_ID');

            $filter = [];

            if (!empty($enrolledCourseIds)) {
                $filter['!@ID'] = $enrolledCourseIds;
            }

            $availableCourses = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES, [
                'filter' => $filter,
                'order' => ['ID' => 'ASC']
            ]);

            return [
                'status' => 'ok',
                'items' => $availableCourses,
            ];
        }

        public static function getCoursesByTeacherId($aRequest)
        {
            $teacherId = intval($aRequest['teacherId'] ?? 0);

            if ($teacherId <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Некорректное значение преподавателя'
                ];
            }

            if (!RolePermissions::isTeacher($teacherId)) {
                return [
                    'status' => 'error',
                    'message' => 'Пользователь не является преподавателем'
                ];
            }

            $courses = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES, [
                'filter' => [
                    '=UF_TEACHER_ID' => $teacherId
                ],
                'order' => [
                    'ID' => 'ASC'
                ]
            ]);

            if (empty($courses)) {
                return [
                    'status' => 'ok',
                    'items' => [],
                    'message' => 'Преподаватель пока не ведёт ни одного курса'
                ];
            }

            return [
                'status' => 'ok',
                'items' => $courses
            ];
        }
    }
}