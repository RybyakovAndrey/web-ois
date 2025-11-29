<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Legacy\HighLoadBlock\Entity;

class Tasks
{
    public static function getByCourseId($aRequest)
    {
        $Id = intval($aRequest['id'] ?? 0);

        if ($Id <= 0) {
            return [
                'status' => 'error',
                'message' => 'Некорректный id'
            ];
        }

        $params = [
            'filter' => ['=UF_COURSE_ID' => $Id],
            'order' => ['ID' => 'ASC'],
        ];

        $result = Entity::getInstance()->getList(Constants::HLBLOCK_TASKS, $params);

        return [
            'status' => 'ok',
            'items'   => $result,
        ];
    }
}