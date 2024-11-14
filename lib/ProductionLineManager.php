<?php

namespace Production\Line;

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;

Loader::includeModule('crm');
Loader::includeModule('iblock');
Loader::includeModule('crm');
Loader::includeModule('main');
Loader::includeModule('bizproc');
Loader::includeModule('production.line');
class ProductionLineManager
{
    static public function groupedDeal($aRCombinations)
    {
        $grouped = array_reduce($aRCombinations, function ($acc, $item) {
            $key = $item['order1_id'] . '-' . $item['countOrder1'] . '-' . $item['withMaterial'];
            if (!isset($acc[$key])) {
                $acc[$key] = [];
            }
            $acc[$key][] = $item;

            return $acc;
        }, []);
        // Проходим по группам и добавляем цвет только тем группам, где больше одного элемента
        $result = [];
        foreach ($grouped as $key => $group) {
            if (count($group) > 1) {
                $color = getRandomColor(rand_color(), 0.8);
                foreach ($group as &$item) {
                    $item['color'] = $color;
                }
            }
            $result = array_merge($result, $group);
        }
        return $result;
    }

    static public function addDeal($id)
    {
        $data = QueueProductionLineTable::getList([
            'select' => [
                'NAME_ORDER_MAIN',
                'NAME_ORDER_COMBINED',
                'MATERIAL_WIDTH',
                'COUNT_ORDER_MAIN',
                'COUNT_ORDER_COMBINED',
                'MAIN_ELEMENT_ID',
                'COMBINED_ELEMENT_ID',
                'EFFICIENCY_PERCENT',
                'PLAN_MAIN_QUANTITY',
                'PLAN_COMBINED_QUANTITY',
                'USED_MAIN_QUANTITY',
                'USED_COMBINED_QUANTITY',
                'REMAINING_MAIN_QUANTITY',
                'REMAINING_COMBINED_QUANTITY',
                'RUNNING_METERS',
                'COLOR',
            ],
            'filter' => [
                '>ID' => '$id'
            ],
            'limit' => 1
        ])->fetch();


        $entityTypeId = \CCrmOwnerType::Deal;
        $factory = Container::getInstance()->getFactory($entityTypeId);
        $new_item = $factory->createItem([
            'TITLE' => $data['NAME_ORDER_MAIN'] . '/' . $data['NAME_ORDER_COMBINED'],
            'STAGE_ID' => 'C9:UC_5O2IAX',
            'CATEGORY_ID' => 9,
            'UF_CRM_1680089010545' => $data['MATERIAL_WIDTH'], //ширина рулона
            'UF_CRM_1680087136' => $data['MAIN_ELEMENT_ID'], // id паспорт
            'UF_CRM_1674156116' => $data['COMBINED_ELEMENT_ID'], // id паспорт сов
            'UF_CRM_1680087517854' => $data['COUNT_ORDER_MAIN'], //Количество основного заказа в ширину
            'UF_CRM_1680088113635' => $data['COUNT_ORDER_COMBINED'], //Количество совмещенного заказа в ширину
            'UF_CRM_1702558329461' => $data['USED_MAIN_QUANTITY'], //Штук на запуск ОСН
            'UF_CRM_1702558337821' => $data['USED_COMBINED_QUANTITY'], //Штук на запуск СОВ
            'UF_CRM_1702558362582' => $data['REMAINING_MAIN_QUANTITY'], //Остается не сделано ОСН
            'UF_CRM_1702558368462' => $data['REMAINING_COMBINED_QUANTITY'], //Остается несделано СОВ
            'UF_CRM_1675555129' => $data['RUNNING_METERS'], //меры погонные заказа
            'UF_CRM_1685005404730' => 1,
            'UF_CRM_1703658554' => $data['COLOR']
        ]);

        $operation = $factory->getAddOperation($new_item, (new \Bitrix\Crm\Service\Context())->setUserId(9));
        $operation->launch();
        return "";
    }

    static public function createAgentCreatingDeal()
    {
        $rows = QueueProductionLineTable::getList([
            'select' => [
                'ID'
            ],
            'order' => [
                'MATERIAL_WIDTH' => 'DESC'
            ]
        ]);

        while ($row = $rows->fetch()) {
            $id = $row['ID'];

            \CAgent::AddAgent(
                "addDeal($id);",  // имя функции
                "main",                // идентификатор модуля
                "N",                      // агент не критичен к кол-ву запусков
                0,                   
                date("d.m.Y h:i:s", time() + 60),                       // дата первой проверки - текущее
                "Y",                      // агент активен
                date("d.m.Y h:i:s", time() + 60),                       // дата первого запуска - текущее
                0
            );
            
        }
    }

    static public function startingBusinessProcess()
    {
        $deals = self::getDeal();
        foreach ($deals as $deal) {
            \CBPDocument::StartWorkflow(
                54,
                ["crm", "CCrmDocumentDeal", "DEAL_" . $deal['ID']],
                ["TargetUser" => "user_1"],
                $arErrorsTmp
            );
        }
    }

    static public function deleteAllDeal($deals): void
    {
        foreach ($deals as $deal) {
            $factory = Container::getInstance()->getFactory(\CCrmOwnerType::Deal);
            $factory->getItem($deal['ID'])->delete();
        }
    }

    static public function getDeal(): array
    {
        $deals = DealTable::getList([
            'filter' => ['STAGE_ID' => 'C9:NEW'],
            'select' => ['ID', 'UF_CRM_1680087136', 'UF_CRM_1674156116',]
        ]);
        return $deals->fetchAll();
    }

    static public function getUnfulfilledOrders($arrIdUnfulfilledOrders)
    {

        $filter = [
            "IBLOCK_ID" => 17,
            "!ID" => $arrIdUnfulfilledOrders,
            "!TIP_UPAKOVKI_VALUE" => false,
            ['LOGIC' => 'OR', "!DATA_OTGRUZKI_VALUE" => false, ">DATA_OTGRUZKI_VALUE" => 0],
            "!MATERIAL_INFO_NAME" => false,
            ['LOGIC' => 'OR', ">RAZVERTKA_SHIRINA_PO_NOZHAM_VALUE" => 0, "!RAZVERTKA_SHIRINA_PO_NOZHAM_VALUE" => false],
            ['LOGIC' => 'OR', ">DLINA_ZAGOTOVKI_VALUE" => 0, "!DLINA_ZAGOTOVKI_VALUE" => false],
            ['LOGIC' => 'OR', ">KOL_VO_NA_SHTAMPE_VALUE" => 0, "!KOL_VO_NA_SHTAMPE_VALUE" => false],
            ['LOGIC' => 'OR', ">KOL_VO_PLAN_SHTUK_VALUE" => 0, "!KOL_VO_PLAN_SHTUK_VALUE" => false],
        ];
        return self::getListOrder($filter);
    }

    /*     static public function setfulfilledOrders($deal){
        $arr = array_unique(array_column($deal, 'UF_CRM_1680087136') + array_column($deal, 'UF_CRM_1674156116'));
        $filter = [
            "IBLOCK_ID" => 17,
            "!NOMER_VALUE" => $arr,
            "!TIP_UPAKOVKI_VALUE" => false,
            ['LOGIC' => 'OR', "!DATA_OTGRUZKI_VALUE" => false, ">DATA_OTGRUZKI_VALUE" => 0],
            "!MATERIAL_INFO_NAME" => false,
            ['LOGIC' => 'OR', ">RAZVERTKA_SHIRINA_PO_NOZHAM_VALUE" => 0, "!RAZVERTKA_SHIRINA_PO_NOZHAM_VALUE" => false],
            ['LOGIC' => 'OR', ">DLINA_ZAGOTOVKI_VALUE" => 0, "!DLINA_ZAGOTOVKI_VALUE" => false],
            ['LOGIC' => 'OR', ">KOL_VO_NA_SHTAMPE_VALUE" => 0, "!KOL_VO_NA_SHTAMPE_VALUE" => false],
            ['LOGIC' => 'OR', ">KOL_VO_PLAN_SHTUK_VALUE" => 0, "!KOL_VO_PLAN_SHTUK_VALUE" => false],
        ];
        return self::getListOrder($filter);
    } */

    static public function updateListOrder($list)
    {
        foreach ($list as $key => $value) {
            \CIBlockElement::SetPropertyValueCode($key, "SDELANO", $value['made']);
            \CIBlockElement::SetPropertyValueCode($key, "OSTALOS_SDELAT", $value['left']);
        }
    }

    static public function getListOrder($filter): array
    {
        $iblockCode = "ProductionSchedule";
        $ibClass = '\Bitrix\Iblock\Elements\Element' . $iblockCode . 'Table';
        $obResult = $ibClass::getList([
            'filter' => $filter,
            'select' => [
                'ID',
                'NAME',
                'NOMER_VALUE' => 'NOMER.IBLOCK_GENERIC_VALUE',
                'DATA_OTGRUZKI_VALUE' => 'DATA_OTGRUZKI.VALUE',
                'MATERIAL_ID' => 'MATERIAL.VALUE',
                'MATERIAL_INFO_NAME' => 'MATERIAL_INFO.VALUE',
                'TIP_UPAKOVKI_VALUE' => 'TIP_UPAKOVKI.VALUE',
                'RAZVERTKA_SHIRINA_PO_NOZHAM_VALUE' => 'RAZVERTKA_SHIRINA_PO_NOZHAM.VALUE',
                'KOL_VO_NA_SHTAMPE_VALUE' => 'KOL_VO_NA_SHTAMPE.VALUE',
                'DLINA_ZAGOTOVKI_VALUE' => 'DLINA_ZAGOTOVKI.VALUE',
                'KOL_VO_PLAN_SHTUK_VALUE' => 'KOL_VO_PLAN_SHTUK.VALUE',
                'KOL_VO_PLAN_SHTUK_VALUE_COPY' => 'KOL_VO_PLAN_SHTUK.VALUE',
                'SROCHNYY_VALUE' => 'SROCHNYY.IBLOCK_GENERIC_VALUE',
                'OSTALOS_SDELAT_VALUE' => 'OSTALOS_SDELAT.VALUE',
            ],
            'order' => [
                'DATA_OTGRUZKI_VALUE' => 'ASC',
            ],
            //Materials
            'runtime' => array(
                'MATERIAL_INFO' => [
                    'data_type' => \Bitrix\Iblock\PropertyEnumerationTable::class,
                    'reference' => ['this.MATERIAL_ID' => 'ref.ID'],
                    'join_type' => 'LEFT'
                ]
            )
        ]);

        $result = $obResult->fetchAll();
        foreach ($result as $key => $value) {

            $result[$key]['RUNNING_METERS'] = getRaningMetrs(
                $result[$key]['KOL_VO_PLAN_SHTUK_VALUE'],
                $result[$key]['KOL_VO_NA_SHTAMPE_VALUE'],
                $result[$key]['DLINA_ZAGOTOVKI_VALUE'],
                $result[$key]['TIP_UPAKOVKI_VALUE']
            );
            $result[$key]['SEQUENCE_NUMBER'] = 1;

            change_key($key, $result[$key]['ID'], $result);
        }

        return $result;
    }

    static public function rand_color(): string
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    static public function getRandomColor($hexCode, $adjustPercent): string
    {
        $hexCode = ltrim($hexCode, '#');
        if (strlen($hexCode) == 3) {
            $hexCode = str_repeat($hexCode[0], 2) . str_repeat($hexCode[1], 2) . str_repeat($hexCode[2], 2);
        }
        return '#' . implode('', array_map(function ($color) use ($adjustPercent) {
            return str_pad(dechex($color + ceil(($adjustPercent < 0 ? $color : 255 - $color) * $adjustPercent)), 2, '0', STR_PAD_LEFT);
        }, sscanf($hexCode, '%02x%02x%02x')));

        /* 
            $hexCode = ltrim($hexCode, '#');

            if (strlen($hexCode) == 3) {
                $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
            }

            $hexCode = array_map('hexdec', str_split($hexCode, 2));

            foreach ($hexCode as &$color) {
                $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
                $adjustAmount = ceil($adjustableLimit * $adjustPercent);

                $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
            }

            return '#' . implode($hexCode); 
        */
    }

    static private function getRunningMeters($kol_vo_plan_shtuk, $kol_vo_na_shtampe, $dlina_zagotovki, $tip_upakovki)
    {
        return ($kol_vo_plan_shtuk * $dlina_zagotovki) / ($kol_vo_na_shtampe ?: 1) * ($tip_upakovki ?: 1);
    }
}
