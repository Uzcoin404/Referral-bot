<?php
date_default_timezone_set('Asia/Tashkent');
require_once('library/Telegram.php');
include_once('user.php');

$telegram = new Telegram("5046375589:AAGr3BvYHe2NiIPMYyzOpJalcdP2UyvC81Y", true);
$Admin = "829349149";

$message = $telegram->getData()['message'];
$chat_id = $telegram ->ChatID();
$text = $telegram ->Text();
$first_name = $telegram -> FirstName();
$last_name = $telegram -> LastName();
$username = $telegram -> Username();
$userData = new UserData($chat_id);

switch ($text) {
    case '/start':
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Salom']);
        break;
    case '/test':
        $userData->setData('data', json_encode($telegram->getData()));
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => 'Saqlandi']);
        break;
    case '/show':
        $data = $userData->getData('data');
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $data]);
    break;
}

function resendMessage($text){
    global $telegram, $chat_id;
    $content = ['chat_id' => $chat_id, 'text' => $text];
    $telegram->sendMessage($content);
}
?>