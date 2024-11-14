<?php

namespace Production\Line;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class QueueProductionLineTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'queue_production_line';
    }

    public static function getMap()
    {
        return [
            (new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ])),
            (new Entity\StringField('NAME_ORDER_MAIN')),
            (new Entity\StringField('NAME_ORDER_COMBINED')),
            (new Entity\IntegerField('EFFICIENCY_PERCENT')),
            (new Entity\IntegerField('MATERIAL_WIDTH')),
            (new Entity\StringField('MATERIAL')),
            (new Entity\IntegerField('MAIN_ELEMENT_ID')),
            (new Entity\IntegerField('COMBINED_ELEMENT_ID')),
            (new Entity\IntegerField('COUNT_ORDER_MAIN')),
            (new Entity\IntegerField('COUNT_ORDER_COMBINED')),
            (new Entity\IntegerField('QUANTITY_WIDTH_MAIN')),
            (new Entity\IntegerField('QUANTITY_WIDTH_COMBINED')),
            (new Entity\IntegerField('REMAINING_MAIN_QUANTITY')),
            (new Entity\IntegerField('REMAINING_COMBINED_QUANTITY')),
            (new Entity\IntegerField('USED_MAIN_QUANTITY')),
            (new Entity\IntegerField('USED_COMBINED_QUANTITY')),
            (new Entity\IntegerField('PLAN_MAIN_QUANTITY')),
            (new Entity\IntegerField('PLAN_COMBINED_QUANTITY')),
            (new Entity\FloatField('RUNNING_METERS')),
            (new Entity\StringField('COLOR'))
        ];
    }

    // Метод для удаления всех записей из таблицы
    public static function deleteAllRecords()
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(static::getTableName());
    }
}
