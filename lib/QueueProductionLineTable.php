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
        ];
    }

    // Метод для удаления всех записей из таблицы
    public static function deleteAllRecords()
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(static::getTableName());
        
        // $connection = Application::getConnection();
        // $tableName = static::getTableName();

        
        // $sql = "DELETE FROM {$tableName}";
        // $connection->queryExecute($sql);
    }

    public static function createTable()
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->queryExecute("
            CREATE TABLE IF NOT EXISTS " . self::getTableName() . " (
                ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                NAME_ORDER_MAIN VARCHAR(255) NOT NULL,
                NAME_ORDER_COMBINED VARCHAR(255) NOT NULL,
                EFFICIENCY_PERCENT INT NOT NULL,
                MATERIAL_WIDTH INT NOT NULL,
                MATERIAL VARCHAR(255) NOT NULL,
                MAIN_ELEMENT_ID INT NOT NULL,
                COMBINED_ELEMENT_ID INT,
                COUNT_ORDER_MAIN INT NOT NULL,
                COUNT_ORDER_COMBINED INT NOT NULL,
                QUANTITY_WIDTH_MAIN INT NOT NULL,
                QUANTITY_WIDTH_COMBINED INT NOT NULL,
                REMAINING_MAIN_QUANTITY INT NOT NULL,
                REMAINING_COMBINED_QUANTITY INT NOT NULL,
                USED_MAIN_QUANTITY INT NOT NULL,
                USED_COMBINED_QUANTITY INT NOT NULL,
                PLAN_MAIN_QUANTITY INT NOT NULL,
                PLAN_COMBINED_QUANTITY INT NOT NULL
            )
        ");

        $connection = \Bitrix\Main\Application::getConnection();
        $connection->isTableExists(self::getTableName());
    }

    public static function dropTable()
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->dropTable(self::getTableName());
        $connection->isTableExists(self::getTableName());
    }
}
