<?php

namespace Legacy\API;
use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Legacy\Iblock\TestInfoBlockTable;

class TestInfoBlock
{
    private static function processData($query)
    {
        $result = [];

        while ($arr = $query->fetch()) {
            $result[] = [
                'id' => $arr['ID'],
                'name' => $arr['NAME'],
            ];
        }

        return $result;
    }

    public static function get($arRequest)
    {
        $result = [];
        if (Loader::includeModule('iblock')) {
            $page = (int)($arRequest['page']);
            $limit = (int)($arRequest['limit']);

            $q = TestInfoBlockTable::query()
                ->countTotal(true)
                ->withSelect()
                ->setLimit($limit)
                ->withPage($page)
                ->withOrderByDate('DESC')
                ->exec()
            ;

            $result['count'] = $q->getCount();
            $result['items'] = self::processData($q);
        }
        return $result;
    }
}