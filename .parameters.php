<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule('iblock')) {
    return;
}

$arIBlock = array();
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y'));
while ($arr = $rsIBlock->Fetch()) {
    $arIBlock[$arr['ID']] = '[' . $arr['ID'] . '] ' . $arr['NAME'];
}

$arEventTypes = array();
$rsEventTypes = CEventType::GetList();
while ($arr = $rsEventTypes->Fetch()) {
    $arEventTypes[$arr['EVENT_NAME']] = '[' . $arr['EVENT_NAME'] . '] ' . $arr['NAME'];
}

$arEvents = array();
$arFilter = isset($arCurrentValues['EVENT_TYPE'])
            ? array('TYPE_ID' => $arCurrentValues['EVENT_TYPE'])
            : array();

$rsEvents = CEventMessage::GetList($by = 'site_id', $order = 'desc', $arFilter);
while ($arr = $rsEvents->Fetch()) {
    $arEvents[$arr['ID']] = '[' . $arr['ID'] . '] ' . $arr['SUBJECT'];
}
$arGroups = array();
$arFilter = array(
    "ID" => "1 | 2",
);
$rsGroups = CGroup::GetList($by="c_sort", $order="desc",$arFilter); // выбираем группы
while ($arr = $rsGroups->Fetch()) {
    $arGroups[$arr['ID']] = '[' . $arr['NAME'] . '] ' . $arr['DESCRIPTION'];
}

$arComponentParameters = array (
	'GROUPS' => [
    'SAVE_TO_IBLOCK' => [
      'NAME' => 'Сохранять в инфоблок',
      'SORT' => '150'
    ],
    'SAVE_TO_CRM' => [
      'NAME' => 'Сохранять в CRM',
      'SORT' => '170'
    ],
    'RECAPTCHA' => [
      'NAME' => 'RECAPTCHA',
      'SORT' => '175'
    ],
    'MSG' => [
      'NAME' => 'Сообщения',
      'SORT' => '180'
    ],
  ],

	'PARAMETERS' => array (
    'FORM_ID' => array(
        'PARENT' => 'BASE',
        'NAME' => 'id формы (любая уникальная строка)',
        'TYPE' => 'STRING',
        'DEFAULT' => 'feedback',
    ),
    'FORM_FIELDS' => array(
        'PARENT' => 'BASE',
        'NAME' => 'Список полей формы (Имя|Метка|Обязательно|Тип поля|Класс тега)',
        'TYPE' => 'LIST',
        'VALUES' => array(),
        'MULTIPLE' => 'Y',
        'ADDITIONAL_VALUES' => 'Y',
        'DEFAULT' => array(
            'NAME|Имя|Y|text|text',
            'PHONE|Телефон|Y|text',
            'MSG|Сообщение|N|textarea',
        )
    ),
    'SUCCESS_MSG' => array(
        'PARENT' => 'MSG',
        'NAME' => 'Сообщение о успешной отправке формы',
        'TYPE' => 'STRING',
        'DEFAULT' => 'Данные успешно отправлены',
    ),
    'ERROR_MSG' => array(
        'PARENT' => 'MSG',
        'NAME' => 'Сообщение о ошибке отправки',
        'TYPE' => 'STRING',
        'DEFAULT' => 'При отправке данных произошла ошибка',
    ),
    'ERROR_FIELD_MSG' => array(
        'PARENT' => 'MSG',
        'NAME' => 'Сообщение валидатора о том что поле не заполнено',
        'TYPE' => 'STRING',
        'DEFAULT' => 'Это поле обязательно для заполнения',
    ),
    'IS_SAVE_TO_IBLOCK' => array(
        'PARENT' => 'SAVE_TO_IBLOCK',
        'NAME' => 'Сохранять результат в инфоблок',
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y',
        "REFRESH" => "Y",
    ),


        'GROUPS_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Группы',
            'MULTIPLE' => 'Y',
            'TYPE' => 'LIST',
            'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arGroups,
        ),

	),
);

if (intval($arCurrentValues['IS_SAVE_TO_IBLOCK'] == 'Y'))
{
  $arComponentParameters['PARAMETERS']['IBLOCK_ID'] = array(
     'PARENT' => 'SAVE_TO_IBLOCK',
     'NAME' => 'Инфоблок в который будет сохраняться результат',
     'TYPE' => 'LIST',
     'VALUES' => $arIBlock,
  );
}
