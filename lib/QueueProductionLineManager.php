<?php
namespace Production\Line;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class QueueProductionLineManager
{
    public static function addRecord($data)
    {
        if (!Loader::includeModule('production.line')) {
            throw new SystemException('Модуль не подключен');
        }

        $result = QueueProductionLineTable::add($data);

        if ($result->isSuccess()) {
            return $result->getId();
        } else {
            throw new SystemException('Ошибка при добавлении записи: ' . implode(', ', $result->getErrorMessages()));
        }
    }

    public static function updateRecord($id, $data)
    {
        if (!Loader::includeModule('production.line')) {
            throw new SystemException('Модуль не подключен');
        }

        $result = QueueProductionLineTable::update($id, $data);

        if ($result->isSuccess()) {
            return $result->getId();
        } else {
            throw new SystemException('Ошибка при обновлении записи: ' . implode(', ', $result->getErrorMessages()));
        }
    }

    public static function deleteRecord($id)
    {
        if (!Loader::includeModule('production.line')) {
            throw new SystemException('Модуль не подключен');
        }

        $result = QueueProductionLineTable::delete($id);

        if ($result->isSuccess()) {
            return true;
        } else {
            throw new SystemException('Ошибка при удалении записи: ' . implode(', ', $result->getErrorMessages()));
        }
    }
}
