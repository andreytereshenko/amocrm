<?php
include 'API.php';

$name = htmlspecialchars($_POST['name'], ENT_NOQUOTES, 'UTF-8');
$phone = htmlspecialchars($_POST['phone'], ENT_NOQUOTES, 'UTF-8');
$email = htmlspecialchars($_POST['email'], ENT_NOQUOTES, 'UTF-8');

$subdomain = 'andretereshenko'; //Наш аккаунт - поддомен
$user = array(
    'USER_LOGIN' => 'andre.tereshenko@gmail.com', //логин (электронная почта)
    'USER_HASH' => '13f293d7791107dde370641cfa82183ce3f417d9' //Хэш для доступа к API
);

$phoneFieldId = '440965'; //ID поля "Телефон"
$emailFieldId = '440967'; //ID поля "Email"
$responsibleId = '2614519'; //ID Ответственного сотрудника

$dealName = 'Заявка с сайта'; //Название создаваемой сделки
$dealStatusID = '20476129'; //ID статуса сделки
$dealSale = '70000'; //Сумма сделки
$dealTags = 'Сделка';  //Теги для сделки
$contactTags = 'Контакт'; //Теги для контакта

$api = new API();

/**
 * Если мы успешно авторизовались, то ищем существующий контакт с email и phone из формы.
 * Если контакт существует, создается новая сделка, контакт привязывается помимо своих сделок в новую
 * Если контакта не существует, просто создается новый контакт с данными из формы.
 */
if ($api->authorize($user,$subdomain) == true ) {
    $contactInfo = $api->findContact($subdomain, $email, $phone);
    $idContact = $contactInfo['idContact'];
    if ($idContact != null) {
       $task = $api-> task($idDeal, $responsibleId, $subdomain);
        $idDeal[] = $api->addDeal($dealName, $dealStatusID, $dealSale, $responsibleId, $dealTags, $subdomain);
        if ($idDeal != null) {
            if (!empty($contactInfo['idLeads'])) {
                foreach ($contactInfo['idLeads'] as $idLeads) {
                    $idDeal[] = $idLeads;
                }
            }
            $api->editContact($idContact, $idDeal, $subdomain);
        }
    } else {
        $api->addContact($name,$responsibleId,$phoneFieldId,$phone,$emailFieldId,$email, $subdomain, $contactTags);
    }
}
header('Location: /');
