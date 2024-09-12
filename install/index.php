<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class production_line extends CModule
{
    public function __construct()
    {
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = 'production.line';
        $this->MODULE_NAME = 'Production Line';
        $this->MODULE_DESCRIPTION = 'Module for managing production lines';

        $this->PARTNER_NAME = 'Advanced Technology';
        $this->PARTNER_URI = 'https://tech-ad.ru';
    }
    public function DoInstall()
    {
        $this->InstallDB();
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        $this->UnInstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallDB()
    {
            global $DB;
            $result = $DB->Query("SHOW TABLES LIKE 'queue_production_line'");
            if ($result->SelectedRowsCount() > 0) {
                // Таблица уже существует, пропускаем создание
                return true;
            }
            
            $errors = $DB->RunSQLBatch(__DIR__ . '/db/install.sql');
            if ($errors) {
                
                //выполняем произвольный запрос
                $name_array=array();
                //создаем пустой массив, но можно эту строчку исключить
                foreach ($errors as $row) {
                    // echo $row['NAME'];//выводим все значения, которые вернул запрос
                    array_push($name_array, $row);//если исключили создание массива, то исключите и эту строку, тут мы создаем массив со всеми значениями которые вернул запрос, чтобы потом этот массив использовать в любых целях.

                }
                // while ($row = $errors->Fetch())
                // {
                //     // echo $row['NAME'];//выводим все значения, которые вернул запрос
                //     array_push($name_array, $row);//если исключили создание массива, то исключите и эту строку, тут мы создаем массив со всеми значениями которые вернул запрос, чтобы потом этот массив использовать в любых целях.
                // }
                global $APPLICATION;
                $APPLICATION->ThrowException(implode('<br>', $errors));
                return false;
            }
            return true;
    }

    public function UnInstallDB()
    {
        global $DB;
        $DB->RunSQLBatch(__DIR__ . '/db/uninstall.sql');
    }


}
