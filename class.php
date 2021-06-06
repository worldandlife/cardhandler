<?php


use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

class CardHandlerComponent extends CBitrixComponent
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


    protected function gUser()
    {
        global $USER;
        return $USER;
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
            if ($field['REQUIRED'] == 'Y' && !$request->getPost($field['NAME'])) {
                $this->errorsValidate[] = $this->arParams['ERROR_FIELD_MSG'] . ': ' . $field['LABEL'];

                $validate = false;
            }

        }

        return $validate;
    }
    /**
     * Производит проверку данных
     */
    protected function validateOverride($field_name)
    {
        $validate = true;
        $request = $this->request();

        foreach ($this->getFormFields() as $field) {
            if ($field['REQUIRED'] == 'Y' && !$request->getPost($field['NAME']) && $field['NAME']==$field_name) {
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
     * Проверяет запрос на post и ajax
     *
     * @return boolean
     */
    protected function isPostData()
    {
        return $this->request()->isPost() && $this->request()->isAjaxRequest();
    }


    /**
     * проверка на принадлежность к группе
     */
    protected function isInGroup($groups)
    {
        // массив групп, которых нужно проверить доступность пользователя
        $arGroupAvailable = $groups;
        // массив групп, в которых состоит пользователь
        $arGroups = CUser::GetUserGroup($this->gUser()->GetID());
        $result_intersect = array_intersect($arGroupAvailable, $arGroups);
        // далее проверяем, если пользователь вошёл хотя бы в одну из групп, то позволяем ему что-либо делать
        if (!empty($result_intersect))
            return true;
        else
            return false;

    }


    protected function getCardBalance($value)
    {
        $arFields = array();
        Loader::includeModule('iblock');

        $arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_CARD_PRICE");
        $arFilter = array("IBLOCK_ID" => 4, "ACTIVE" => "N", array("=PROPERTY_CARD_NUMBER" => $value));
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNext()) {
            $arFields = $ob;
        }
            return $arFields['PROPERTY_CARD_PRICE_VALUE'];
    }
    protected function getCardElementId($value)
    {
        $arFields = array();
        Loader::includeModule('iblock');

        $arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_CARD_PRICE");
        $arFilter = array("IBLOCK_ID" => 4, "ACTIVE" => "N", array("=PROPERTY_CARD_NUMBER" => $value));
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNext()) {
            $arFields = $ob;
        }
        return $arFields['ID'];
    }

    protected function transaction($cardNumber, $debitSum)
    {
        $balance = $this->getCardBalance($cardNumber);
        if ($balance >= $debitSum && $debitSum>0) {
            $balance -= $debitSum;

            $ELEMENT_ID = $this->getCardElementId($cardNumber);  // код элемента
            $PROPERTY_CODE = "CARD_PRICE";  // код свойства
            Loader::includeModule('iblock');
            // Установим новое значение для данного свойства данного элемента
            CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, 4, array($PROPERTY_CODE => 0));

            return true;
        }
        return false;
    }


    public function switcher($method)
    {


        switch ($method) {

            case "getCardBalance":
                if (!$this->validateOverride('CARD_NUMBER')) {
                    $this->jsonResponse([
                        'msg' => implode('<br>', $this->getErrorsValidate()),
                        'type' => 'error'
                    ]);

                    return;
                }
                $res = self::getCardBalance($this->request()->getPost('CARD_NUMBER'));
                if (empty($res)) {
                    $this->jsonResponse([
                        'msg' => $this->arParams['ERROR_CARD_NOT_FOUND'],
                        'type' => 'error'
                    ]);
                }
                $this->jsonResponse([
                    'msg' => $res,
                    'type' => 'ok'
                ]);
                break;
            case "transaction":
                if (!$this->validate()) {
                    $this->jsonResponse([
                        'msg' => implode('<br>', $this->getErrorsValidate()),
                        'type' => 'error'
                    ]);

                    return;
                }
                $res = self::transaction($this->request()->getPost('CARD_NUMBER'), $this->request()->getPost('CARD_DEBIT'));
                if (empty($res)) {
                    $this->jsonResponse([
                        'msg' => $this->arParams['ERROR_DEBIT_SUM'],
                        'type' => 'error'
                    ]);
                }
                $this->jsonResponse([
                    'msg' => $res,
                    'type' => 'ok'
                ]);

                break;
        }

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

//
//            $this->sendMail();
//            $this->saveInIblock();
//            $this->jsonResponse([
//                'msg' => $this->arParams['SUCCESS_MSG'],
//                'type' => 'ok'
//            ]);

            $this->switcher($this->request()->getPost('method'));




            return;
        }

        $this->arResult['FORM_FIELDS'] = $this->getFormFields();
        $this->arResult['FORM_FIELDS_HIDDEN'] = [
            'EVENT_ID' => $this->arParams['EVENT_ID'],
            'FORM_ID' => $this->arParams['FORM_ID']
        ];

        $this->includeComponentTemplate();
    }
}
