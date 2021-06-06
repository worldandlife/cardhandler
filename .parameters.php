<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('iblock')) {
    return;
}

$arIBlock = array();
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y'));
while ($arr = $rsIBlock->Fetch()) {
    $arIBlock[$arr['ID']] = '[' . $arr['ID'] . '] ' . $arr['NAME'];
}
$arGroups = array();
$arFilter = array(
    "ID" => "1 | 2",
);
$rsGroups = CGroup::GetList($by = "c_sort", $order = "desc", $arFilter); // выбираем группы
while ($arr = $rsGroups->Fetch()) {
    $arGroups[$arr['ID']] = '[' . $arr['NAME'] . '] ' . $arr['DESCRIPTION'];
}

$arComponentParameters = array(
    'GROUPS' => [
        'SAVE_TO_IBLOCK' => [
            'NAME' => 'Сохранять в инфоблок',
            'SORT' => '150'
        ],
        'IBLOCK_FOR_SEARCH' => [
            'NAME' => 'Искать в инфоблоке',
            'SORT' => '150'
        ],
        'ACCESS_PARAMS' => [
            'NAME' => 'Параметры доступа',
            'SORT' => '150'
        ],
        'MSG' => [
            'NAME' => 'Сообщения',
            'SORT' => '180'
        ],
    ],

    'PARAMETERS' => array(
        'FORM_FIELDS' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Список полей формы (Имя|Метка|Обязательно|Тип поля|Класс тега)',
            'TYPE' => 'LIST',
            'VALUES' => array(),
            'MULTIPLE' => 'Y',
            'ADDITIONAL_VALUES' => 'Y',
            'DEFAULT' => array(
                'CARD_NUMBER|Номер карты|Y|text',
                'CARD_DEBIT|Сумма списания|Y|text'
            )
        ),
        'SUCCESS_MSG' => array(
            'PARENT' => 'MSG',
            'NAME' => 'Сообщение об успешном списании с карты',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Списание прошло успешно',
        ),
        'ERROR_CARD_NOT_FOUND' => array(
            'PARENT' => 'MSG',
            'NAME' => 'Сообщение о ошибке поиска',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Ошибка, такой карты не существует или она аннулирована',
        ),
        'ERROR_DEBIT_SUM' => array(
            'PARENT' => 'MSG',
            'NAME' => 'Сообщение о ошибке списания',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Ошибка, сумма списания должна быть меньше либо равна балансу',
        ),
        'ERROR_FIELD_MSG' => array(
            'PARENT' => 'MSG',
            'NAME' => 'Сообщение валидатора о том что поле не заполнено',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Это поле обязательно для заполнения',
        ),


    ),
);


$arComponentParameters['PARAMETERS']['IBLOCK_ID'] = array(
    'PARENT' => 'SAVE_TO_IBLOCK',
    'NAME' => 'Инфоблок в который будет сохраняться история списаний по карте',
    'TYPE' => 'LIST',
    'VALUES' => $arIBlock,
);
$arComponentParameters['PARAMETERS']['IBLOCK_FOR_SEARCH'] = array(
    'PARENT' => 'IBLOCK_FOR_SEARCH',
    'NAME' => 'Инфоблок в котором будет осуществляться поиск карты',
    'TYPE' => 'LIST',
    'VALUES' => $arIBlock,
);
$arComponentParameters['PARAMETERS']['ACCESS_PARAMS'] = array(

    'PARENT' => 'ACCESS_PARAMS',
    'NAME' => 'Страница, на котрой подключен компонент будет показана только выбранным группам пользователей',
    'MULTIPLE' => 'Y',
    'TYPE' => 'LIST',
    'ADDITIONAL_VALUES' => 'Y',
    'VALUES' => $arGroups,


);

