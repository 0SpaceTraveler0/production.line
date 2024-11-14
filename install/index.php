<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Production\Line\QueueProductionLineTable;
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
        COption::SetOptionString("production.line", "totalMileage");
        COption::SetOptionString("production.line", "last_processed_deal_id");
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        $this->UnInstallDB();
        COption::RemoveOption("production.line", "totalMileage");
        COption::RemoveOption("production.line", "last_processed_deal_id");
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallDB()
    {
        global $DB;
        $result = $DB->Query("SHOW TABLES LIKE 'queue_production_line'");
        if ($result->SelectedRowsCount() > 0) {
            return true;
        }

        $errors = $DB->RunSQLBatch(__DIR__ . '/db/install.sql');
        if ($errors) {
            global $APPLICATION;
            $APPLICATION->ThrowException(implode('<br>', $errors));
            file_put_contents(__DIR__."/error_instal_bd.txt", print_r($errors, true), FILE_APPEND);
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
