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
     * Проверка наличия модулей требуемых для работы компонента
     * @return bool
     * @throws Exception
     */
    private function checkModules()
    {
        if (!Loader::includeModule('iblock')
        ) {
            throw new \Exception('Не загружены модули необходимые для работы компонента');
        }

        return true;
    }

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
            if ($field['REQUIRED'] == 'Y' && !$request->getPost($field['NAME']) && $field['NAME'] == $field_name) {
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


    //
    function saveInIblock($props)
    {
        // это подключит нужный класс для работы с инфоблоком
        global $USER;
        // обязательно указываем класс
        $el = new CIBlockElement;
        $arLoadProductArray = array(
            // обязательно нужно указать дату начала активности элемента
            "ACTIVE_FROM" => date('d.m.Y H:i:s'),
            // указываем какой пользователь добавил элемент
            "MODIFIED_BY" => $USER->GetID(),
            // В корне или нет
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => 5,
            //  собственно сам id блока куда будем добавлять новый элемент
            "NAME" => 'Списание по карте № ' . $props['CARD_NUMBER'],
            // активен или  N не активен
            "ACTIVE" => "N",
            // Добавим нашему элементу заданные свойства
            "PROPERTY_VALUES" => $props,
            // ссылка на детальную картинку
        );
        // с помощью Add добавляем новый элемент
        $el->Add($arLoadProductArray);


    }

    protected function getCardHistoryByNumber($number)
    {
        $arFields = array();
        $arSelect = array(
            "ID",
            "IBLOCK_ID",
            "NAME",
            "DATE_ACTIVE_FROM",
            "PROPERTY_CARD_NUMBER",
            "PROPERTY_INITIAL_CARD_PRICE",
            "PROPERTY_DEBIT_CARD_PRICE",
            "PROPERTY_REMAINING_CARD_PRICE"

        );
        $arFilter = array("IBLOCK_ID" => 5, "ACTIVE" => "N", array("=PROPERTY_CARD_NUMBER" => $number));
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNext()) {
            $arFields[] = $ob;
        }
        return $arFields;
    }

    protected function getCardFieldsByNumber($number)
    {
        $arFields = array();
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_CARD_PRICE");
        $arFilter = array("IBLOCK_ID" => 4, "ACTIVE" => "N", array("=PROPERTY_CARD_NUMBER" => $number));
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNext()) {
            $arFields = $ob;
        }
        return $arFields;
    }

    protected function getCardBalance($number)
    {
        $res = $this->getCardFieldsByNumber($number);
        return $res['PROPERTY_CARD_PRICE_VALUE'];
    }

    protected function getCardElementId($number)
    {
        $res = $this->getCardFieldsByNumber($number);
        return $res['ID'];
    }

    protected function transaction($cardNumber, $debitSum)
    {
        $balance = $this->getCardBalance($cardNumber);
        $initialBalance = $balance;
        if ($balance >= $debitSum && $debitSum > 0) {
            $balance -= $debitSum;

            $ELEMENT_ID = $this->getCardElementId($cardNumber);  // код элемента
            $PROPERTY_CODE = "CARD_PRICE";  // код свойства
            $balance = strval($balance);
            // Установим новое значение для данного свойства данного элемента
            CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, 4, array($PROPERTY_CODE => $balance));

            $props = [
                'CARD_NUMBER' => $cardNumber,
                'INITIAL_CARD_PRICE' => $initialBalance,
                'DEBIT_CARD_PRICE' => $debitSum,
                'REMAINING_CARD_PRICE' => $balance
            ];
            $this->saveInIblock($props);

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
                $res = $this->getCardBalance($this->request()->getPost('CARD_NUMBER'));
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
                $res = $this->transaction($this->request()->getPost('CARD_NUMBER'), $this->request()->getPost('CARD_DEBIT'));
                if (!$res) {
                    $this->jsonResponse([
                        'msg' => $this->arParams['ERROR_DEBIT_SUM'],
                        'type' => 'error'
                    ]);
                }
                $history = $this->getCardHistoryByNumber($this->request()->getPost('CARD_NUMBER'));
                $msg = '';
                foreach ($history as $item) {
                    $msg .= '<tr></tr><td>' . $item['DATE_ACTIVE_FROM'] . '</td>' .
                        '<td>' . $item['PROPERTY_INITIAL_CARD_PRICE_VALUE'] . '</td>' .
                        '<td>' . $item['PROPERTY_DEBIT_CARD_PRICE_VALUE'] . '</td>' .
                        '<td>' . $item['PROPERTY_REMAINING_CARD_PRICE_VALUE'] . '</td></tr>';
                }
                $this->jsonResponse([
                    'msg' => '<table><caption>' . $this->arParams['SUCCESS_MSG'] . '</caption><tr>
                            <th>Дата</th>
                            <th>Изначально</th>
                            <th>Списано</th>
                            <th>Осталось</th>
                            </tr>' . $msg . '</table>',
                    'type' => 'ok'
                ]);

                break;
        }

    }

    public function executeComponent()
    {
        $this->checkModules();
        if (!$this->isInGroup($this->arParams['ACCESS_PARAMS']))
            return;
        if ($this->isPostData()) {
            $this->switcher($this->request()->getPost('method'));
            return;
        }
        $this->arResult['FORM_FIELDS'] = $this->getFormFields();
        $this->includeComponentTemplate();
    }
}
