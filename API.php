<?php

class API
{
    /**
     * Функуция авторизации скрипта на amocrm.
     * @param $user {array} - массив с логином пользователя и hash api ключем
     * @param $subdomain {string} - поддомен, по которому имеем доступ к amocrm
     */

    public function authorize($user, $subdomain){
        #Формируем ссылку для запроса
        $link = 'https://' . $subdomain . '.amocrm.ru/private/api/auth.php?type=json';
        /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP).
        Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP. */
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);#Завершаем сеанс cURL
        /* Теперь мы можем обработать ответ, полученный от сервера. Это пример.
         Вы можете обработать данные своим способом. */
        $code = (int)$code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            if ($code != 200 && $code != 204)
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        } catch (Exception $E) {
            die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
        }
        $Response=json_decode($out,true);
        $Response=$Response['response'];
        if(isset($Response['auth'])) #Флаг авторизации доступен в свойстве "auth"
            return 'Авторизация прошла успешно';
        return 'Авторизация не удалась';
    }


    /**
     * Функция изменения существующего контакта - привязывает контакта к сделке
     * @param $idContact {number} - ID контакта
     * @param $idDeal {number} - ID сделки
     * @param $subdomain {string} - поддомен для доступа к amocrm
     * @return int - id измененного пользователя.
     */
    public function editContact($idContact, $idDeal, $subdomain){
        $contacts['update'] = array(
            array(
                'id' => $idContact,
                'updated_at' => time(),
                'leads_id' => $idDeal,
            )
        );

        $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/contacts';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $code = (int)$code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            if ($code != 200 && $code != 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
            }
        } catch (Exception $E) {
            die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
        }
        $Response = json_decode($out, true);
        $Response = $Response['_embedded']['items'][0]['id'];
        return $Response;
    }

    /**
     * Функция поиска существующего контакта
     * @param $subdomain {string} - поддомен для доступа к amocrm
     * @param $email {string} - email пользователя
     * @return array - массив с id пользователя и со списком сделок, к которым он привязан
     */

   public function findContact($subdomain, $email, $phone){
        if (($link = 'https://' . $subdomain . '.amocrm.ru/api/v2/contacts/?query=' . $email)== true) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $code = (int)$code;
            $errors = array(
                301 => 'Moved permanently',
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable'
            );
            try {
                if ($code != 200 && $code != 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
                }
            } catch (Exception $E) {
                die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
            }
            $Response = json_decode($out, true);
            $Response = $Response['_embedded']['items'][0];
            $Response['idContact'] = $Response['id'];
            $Response['idLeads'] = $Response['leads']['id'];
            return $Response;
        }
        elseif (($link = 'https://' . $subdomain . '.amocrm.ru/api/v2/contacts/?query=' . $phone)==true){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $code = (int)$code;
            $errors = array(
                301 => 'Moved permanently',
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable'
            );
            try {
                if ($code != 200 && $code != 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
                }
            } catch (Exception $E) {
                die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
            }
            $Response = json_decode($out, true);
            $Response = $Response['_embedded']['items'][0];
            $Response['idContact'] = $Response['id'];
            $Response['idLeads'] = $Response['leads']['id'];
            return $Response;

        }
    }

    /**
     * Функция добавления нового контакта. Привязывается мобильный телефон и рабочий Email.
     * @param $name {string} - имя пользователя
     * @param $responsibleId {number} - ID ответственного и создателя
     * @param $phoneFieldId {number} - ID кастомного поля "Телефон"
     * @param $phone {string} - телефон пользователя
     * @param $emailFieldId {number} - ID кастомного поля "Email"
     * @param $email {string} - email пользователя
     * @param $subdomain {string} - поддомен для доступа к amocrm
     * @return int - ID добавленного пользователя
     */

    function addContact($name, $responsibleId, $phoneFieldId, $phone, $emailFieldId, $email, $subdomain, $contactTags){
        $contacts['add'] = array(
            array(
                'name' => $name,
                'responsible_user_id' => $responsibleId,
                'created_by' => $responsibleId,
                'created_at' => time(),
                'tags' => $contactTags, //Теги
                'custom_fields' => array(
                    array(
                        'id' => "$phoneFieldId",
                        'values' => array(
                            array(
                                'value' => "$phone",
                                'enum' => "MOB"
                            )
                        )
                    ),
                    array(
                        'id' => $emailFieldId,
                        'values' => array(
                            array(
                                'value' => $email,
                                'enum' => "WORK"
                            )
                        )
                    ),
                ),
            )
        );
        $link = '';
        $link .= 'https://' . $subdomain . '.amocrm.ru/api/v2/contacts';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $code = (int)$code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            if ($code != 200 && $code != 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
            }
        } catch (Exception $E) {
            die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
        }

        $Response = json_decode($out, true);
        $Response = $Response['_embedded']['items'][0]['id'];
        return $Response;
    }

    /**Функция создания новой сделки
     * @param $subdomain - поддомен для доступа к amocrm
     * @param $responsibleId
     * @return mixed
     */

   public function addDeal($dealName, $dealStatusID, $dealSale, $responsibleId, $dealTags, $subdomain){
        $leads['add'] = array(
            array(
                'name' => $dealName, //имя сделки
                'created_at' => time(), // время создания сделки
                'status_id' => $dealStatusID, // статус сделки
                'sale' => $dealSale, // сумма сделки
                'responsible_user_id' => $responsibleId,
                'tags' => $dealTags // тег сделки
            ),
        );

        $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/leads';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $code = (int)$code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            if ($code != 200 && $code != 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
            }
        } catch (Exception $E) {
            die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
        }
        $Response = json_decode($out, true);
        $Response = $Response['_embedded']['items'][0]['id'];
        return $Response;
    }

    /**
     * Функция создания новой задачи
     * @param $idDeal - id сделки
     * @param $responsibleId - ID ответственного и создателя
     * @param $subdomain - поддомен для доступа к amocrm
     */

    public function task($idDeal,$responsibleId,$subdomain){
        $tasks['add']=array(
            #Привязываем к сделке
            array(
                'element_id'=>$idDeal, #ID сделки
                'element_type'=>2, #Показываем, что это - сделка, а не контакт
                'task_type'=>1, #Звонок
                'text'=>'Task №1',
                'responsible_user_id'=>$responsibleId,
                'complete_till_at'=>time()+86340
            )
        );
        /* Теперь подготовим данные, необходимые для запроса к серверу */
#Формируем ссылку для запроса
        $link='https://'.$subdomain.'.amocrm.ru/api/v2/tasks';
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
#Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($tasks));
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt');
        curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt');
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
        /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
        $code=(int)$code;
        $errors=array(
            301=>'Moved permanently',
            400=>'Bad request',
            401=>'Unauthorized',
            403=>'Forbidden',
            404=>'Not found',
            500=>'Internal server error',
            502=>'Bad gateway',
            503=>'Service unavailable'
        );
        try
        {
            #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
            if($code!=200 && $code!=204)
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
        }
        catch(Exception $E)
        {
            die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
    }
}

