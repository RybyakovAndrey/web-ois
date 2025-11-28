<?php

namespace Legacy\Iblock;

use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Main\DB\SqlExpression;
use Legacy\General\Constants;


class TestInfoBlockTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query){
        $query
            ->where("IBLOCK_ID", Constants::IB_TEST_INFOBLOCK)
            ->where("ACTIVE", true);
    }

    public static function withSelect(Query $query)
    {
        $query->registerRuntimeField(
            'NAME_PROP',
            new ReferenceField(
                'NAME_PROP',
                ElementPropertyTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_TEST_INFOBLOCK_NAME),
                ]
            )
        );

        $query->setSelect([
            'ID',
            'NAME',
            'NAME_VALUE' => 'NAME_PROP.VALUE',
        ]);
    }

    public static function withPage(Query $query, int $page)
    {
        if ($page > 0) {
            $query->setOffset(($page - 1) * $query->getLimit());
        }
    }

    public static function withOrderByDate(Query $query, $order){
        $query->addOrder('ACTIVE_FROM', $order);
    }

}