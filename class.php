<?php

/**
 * Bitrix component form (webgsite.ru)
 * Компонент для битрикс, создание форм
 *
 * @author    Falur <ienakaev@ya.ru>
 * @link      https://github.com/falur/bitrix.com.form
 * @copyright 2015 - 2016 webgsite.ru
 * @license   GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
 */

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

class FormComponent extends CBitrixComponent
{
    /**
     * Массив с ошибками валидации
     *
     * @var array
     */
    protected $errorsValidate = [];


    /**
     * Возарвщвет глобальный класс приложения битрикс
     *
     * @return CMain
     * @global CMain $APPLICATION
     */
    protected function gApp()
    {
        global $APPLICATION;
        return $APPLICATION;
    }

    /**
     * Запрос
     *
     * @return Bitrix\Main\Request
     */
    protected function request()
    {
        if (isset($this->request)) {
            return $this->request;
        }

        return Application::getInstance()->getContext()->getRequest();
    }

    /**
     * Ответ
     *
     * @return Bitrix\Main\Response
     */
    protected function response()
    {
        return Application::getInstance()->getContext()->getResponse();
    }

    /**
     * Сервер
     *
     * @return Bitrix\Main\Server
     */
    protected function server()
    {
        return Application::getInstance()->getContext()->getServer();
    }

    /**
     * Отдает json ответ
     *
     * @param array $result
     */
    protected function jsonResponse(array $result)
    {
        $this->gApp()->RestartBuffer();
        while (ob_end_clean()) {
        }
        $this->response()->addHeader('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    }

    /**
     * Производит проверку данных
     */
    protected function validate()
    {
        $validate = true;
        $request = $this->request();

        foreach ($this->getFormFields() as $field) {
            if ($field['REQUIRED'] == 'Y' && !$request->getPost($field['NAME']) && $field['TYPE'] != 'file') {
                $this->errorsValidate[] = $this->arParams['ERROR_FIELD_MSG'] . ': ' . $field['LABEL'];

                $validate = false;
            }

            if ($field['REQUIRED'] == 'Y' && $field['TYPE'] == 'file' && !isset($_FILES[$field['NAME']])) {
                $this->errorsValidate[] = $this->arParams['ERROR_FIELD_MSG'] . ': ' . $field['LABEL'];

                $validate = false;
            }
        }

        return $validate;
    }

    /**
     * Возвращает массив с ошибками валидации
     *
     * @return array
     */
    protected function getErrorsValidate()
    {
        return $this->errorsValidate;
    }

    /**
     * Проверяет запрос на post и ajax
     *
     * @return boolean
     */
    protected function isPostData()
    {
        return $this->request()->isPost() && $this->request()->isAjaxRequest();
    }

    /**
     * Возвращает массив с полями формы
     *
     * @return array
     */
    protected function getFormFields()
    {
        $formFields = [];

        foreach ($this->arParams['FORM_FIELDS'] as $field) {
            if (empty($field)) {
                continue;
            }

            list($name, $label, $isRequired, $type, $htmlClass, $tag_id) = explode('|', $field);
            $formFields[] = [
                'NAME' => $name,
                'LABEL' => $label,
                'REQUIRED' => $isRequired,
                'TYPE' => $type,
                'HTML_CLASS' => $htmlClass,
                'ID' => $tag_id
            ];
        }

        return $formFields;
    }


    /**
     * Сохраняет результаты в инфоблок
     */
    protected function saveInIblock()
    {
        if ($this->arParams['IS_SAVE_TO_IBLOCK'] == 'Y') {
            Loader::includeModule('iblock');

            $mapping = $this->getAddIblockMapping();

            if (!empty($mapping)) {
                $fields = [];

                foreach ($mapping as $iblockField => $formField) {
                    if (stripos($iblockField, 'PROPERTY_') !== false) {
                        $propName = str_replace('PROPERTY_', '', $iblockField);
                        $fields['PROPERTY_VALUES'][$propName] = $this->getMacrosValue($formField);
                    } else {
                        $fields[$iblockField] = $this->getMacrosValue($formField);
                    }
                }

                $fields['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];

                if (!isset($fields['NAME'])) {
                    throw new Exception('NAME is required field');
                }
            } else {
                $data = CEventMessage::GetByID($this->arParams['EVENT_ID'])->Fetch();
                $text = $data['MESSAGE'];

                foreach ($this->getFormFields() as $field) {
                    $text = str_replace(
                        '#' . $field['NAME'] . '#',
                        $this->request()->getPost($field['NAME']),
                        $text
                    );
                }

                $fields = [
                    'NAME' => $data['SUBJECT'] . ': ' . date('d.m.Y H:i:s'),
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                    'PREVIEW_TEXT' => $text,

                ];

                foreach ($this->files as $id => $file) {
                    if ('PREVIEW_PICTURE' == $id || 'DETAIL_PICTURE' == $id) {
                        $fields[$id] = $file;
                    } else {
                        $fields['PROPERTY_VALUES'][$id] = $file;
                    }
                }
            }

            $fields['IBLOCK_SECTION_ID'] = isset($fields['IBLOCK_SECTION_ID'])
                ? $fields['IBLOCK_SECTION_ID']
                : false;

            $fields['CODE'] = isset($fields['CODE']) ? $fields['CODE'] : CUtil::translit($fields['NAME'], 'ru');
            $fields['ACTIVE'] = 'N';
            $el = new CIBlockElement;
            $el->Add($fields);
        }
    }

    protected function getAddIblockMapping()
    {
        if (!isset($this->arParams['ADD_IBLOCK_MAPPING']) || empty($this->arParams['ADD_IBLOCK_MAPPING'])) {
            return [];
        }

        $res = [];

        foreach ($this->arParams['ADD_IBLOCK_MAPPING'] as $item) {
            list($iblock, $field) = explode('|', $item);

            $res[$iblock] = $field;
        }

        return $res;
    }

    /**
     * проверка на принадлежность к группе
     */
    protected function isInGroup($groups)
    {
        global $USER;
        // массив групп, которых нужно проверить доступность пользователя
        $arGroupAvailable = $groups;
        // массив групп, в которых состоит пользователь
        $arGroups = CUser::GetUserGroup($USER->GetID());
        $result_intersect = array_intersect($arGroupAvailable, $arGroups);
        // далее проверяем, если пользователь вошёл хотя бы в одну из групп, то позволяем ему что-либо делать
        if (!empty($result_intersect))
            return true;
        else
            return false;

    }



    function checkCard($value)
    {
        if(empty($value))
            return false;
        $arFields = array();
        Loader::includeModule('iblock');

            $arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_CARD_PRICE");
            $arFilter = array("IBLOCK_ID" => 4, "ACTIVE" => "N", array("=PROPERTY_CARD_NUMBER" => $value));
            $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
            while ($ob = $res->GetNext()) {
                $arFields = $ob;
            }
            if(empty($arFields))
                return false;
            else
                return $arFields['PROPERTY_CARD_PRICE_VALUE'];


    }


    function transaction($sum, $debitSum)
    {
        if($sum >= $debitSum)
        {
            $sum -=$debitSum;


        $ELEMENT_ID = 105;  // код элемента
        $PROPERTY_CODE = "CARD_PRICE";  // код свойства
        $PROPERTY_VALUE = "1000";  // значение свойства
        Loader::includeModule('iblock');
        // Установим новое значение для данного свойства данного элемента
        CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, 4, array($PROPERTY_CODE => $sum));

        }

    }


    public function switcher ($method) {


        switch ($method) {

            case "checkCard":
                $res = self::checkCard($this->request()->getPost('CARD_NUMBER'));
                break;
            case "transaction":
                $res = self::transaction($this->request()->getPost('CARD_PRICE'),
                    $this->request()->getPost('CARD_DEBIT'));
                break;
        }

        return $res;
    }
    public function executeComponent()
    {
//        if (!$this->isInGroup($this->arParams['GROUPS_ID']))
//            return;








        if ($this->isPostData()) {

//            if ($this->arParams['FORM_ID'] != $this->request()->getPost('FORM_ID')) {
//                return;
//            }
//
//            if (!$this->validate()) {
//                $this->jsonResponse([
//                    'msg' => implode('<br>', $this->getErrorsValidate()),
//                    'type' => 'error'
//                ]);
//
//                return;
//            }
//
//            $this->sendMail();
//            $this->saveInIblock();
//            $this->jsonResponse([
//                'msg' => $this->arParams['SUCCESS_MSG'],
//                'type' => 'ok'
//            ]);

                $result = $this->switcher($this->request()->getPost('method'));
                if(!$result)
                {
                    $this->jsonResponse([
                        'msg' => "Ошибка, такой карты не существует",
                        'type' => 'error'
                    ]);
                }

                $this->jsonResponse([
                'msg' => $result,
                'type' => 'ok'
            ]);


            return;
        }

//        $this->arResult['FORM_FIELDS_HIDDEN'] = [
//            'EVENT_ID' => $this->arParams['EVENT_ID'],
//            'FORM_ID' => $this->arParams['FORM_ID']
//        ];

        $this->includeComponentTemplate();
    }
}
