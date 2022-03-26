<?php
date_default_timezone_set('Asia/Tashkent');
require_once('library/Telegram.php');
require_once('db.php');
include_once('user.php');

$telegram = new Telegram("5046375589:AAGr3BvYHe2NiIPMYyzOpJalcdP2UyvC81Y", true);
$db = new Database();
$Admin = "829349149";
$Channel = "@UzcoinOfficial";

// $telegram->deleteWebhook();
// if ($telegram->endpoint('getWebhookInfo', [])['result']['url'] == '') {
    
    // $telegram->setWebhook('https://0d6c-91-247-59-95.ngrok.io/referral-bot/');
// }

$message = isset($telegram->getData()['message']) ? $telegram->getData()['message'] : '';
$chatID = $telegram ->ChatID();
$messageID = $telegram ->MessageID();
$text = $telegram ->Text();
$firstName = $telegram -> FirstName();
$lastName = $telegram -> LastName();
$fullName = $firstName . ' ' . $lastName;
$username = $telegram -> Username();
$userData = new UserData($chatID);

if (channelMemberCheck()) {
    if ($text == '/start') {
        setUser();
        showMain();
    } else if (str_contains($text, '/start')) {
        setReferral();
    } else {
        switch ($text) {
            case "📱 Kabinet":
                showCabinet();
                break;
            case "👥 Hamkorlar":
                showReferrals();
                break;
            case "🎲 O'yinlar":
                showGames();
                break;
            case "📚 Bot haqida":
                showAboutBot();
                break;
            case "lotereya":
                sendMessage("Coming soon. O'yin tez orada taqdim etiladi. Omadingizni boshqa o'yinlarda sinashingiz mumkin");
                break;
            case "random":
                startRandomGame();
                break;
            case "randomGame":
                randomGame();
                break;
            default:
                sendMessage("_Botda bunday Buyruq yo'q Iltimos tugmalardan birini bosing yoki /start bosing_", true);
                break;
        }
    }
} else {
    showJoinChannel();
}

function channelMemberCheck(){
    global $telegram, $chatID, $Channel;

    $memberStatus = ['creator', 'administrator', 'member'];
    $chatMember = $telegram->getChatMember(['chat_id' => $Channel, 'user_id' => $chatID]);
    if (in_array($chatMember['result']['status'], $memberStatus)) {
        return true;
    }
    return false;
}

function showJoinChannel(){
    global $telegram, $chatID;
    
    $channelLink = $telegram->buildInlineKeyBoard([[$telegram->buildInlineKeyboardButton("Kanalga azo bo'lish", "https://t.me/UzcoinOfficial")]]);
    $text = "*Iltimos botdan foydalanish uchun ushbu kanalga a'zo bo'ling*";
    sendMessage($text, true, $channelLink);
}

function setUser(){
    global $db, $chatID, $fullName;

    if (!$db->getUser($chatID)) {
        $db->setUser($chatID, $fullName, time());
    }
}

function setReferral(){
    global $db, $chatID, $text;
    $referralCode = substr($text, 7);
        
    if (!$db->getUser($chatID)) {
        $user = $db->getUser($referralCode);
        $name = $user['full_name'];
        if ($user) {
            $db->addReferral($user['id'], $user['referrals'], $user['balance']);
            sendMessage("_Sizni [*$name*](tg://user?id=$referralCode) taklif qildi_", true, null);
        } else {
            sendMessage("_Sizni *Nomala'lum foydalanuvchi* taklif qildi_", true, null);
        }
    }
    setUser();
    showMain();
}

function showMain(){
    global $telegram, $chatID, $userData;

    $mainButtons = $telegram->buildKeyBoard([
        [$telegram->buildKeyboardButton("📱 Kabinet")],
        [$telegram->buildKeyboardButton("👥 Hamkorlar"), $telegram->buildKeyboardButton("🎲 O'yinlar")],
        [$telegram->buildKeyboardButton("📚 Bot haqida")]
    ]);

    $text = "*🚀 Botga xush kelibsiz*";
    sendMessage($text, true, $mainButtons);
    $userData->setData('page', 'main');
}

function showCabinet(){
    global $chatID, $db;
    $user = $db->getUser($chatID);

    $date = $user['date'];
    $daysOnBot = abs(round((time() - $date) / 86400));
    $addedDate = date('Y.m.d H:i', $date);
    $referrals = $user['referrals'];
    $balance = $user['balance'];

    $text = "📱Mening Kabinetim: \n➖➖➖➖➖➖➖➖➖➖ \n🕜 Botda bo'lgan kunlarim: $daysOnBot kun \n🔑 Mening ID: $chatID \n👤 Referallarim: $referrals ta\n➖➖➖➖➖➖➖➖➖➖ \n💳 Balans: $balance som \n🗓 Qo'shilgan sana: $addedDate";
    sendMessage($text);
}

function showReferrals(){
    global $chatID, $db;
    $user = $db->getUser($chatID);
    $referrals = $user['referrals'];

    if ($referrals == 0) {
        $yourReferrals = "👥 Siz birorta ham Referal taklif qilmadingiz";
    } else {
        $yourReferrals = "👥 Sizning taklif qilgan Referallaringiz: $referrals ta";
    }
    $text = "$yourReferrals \n\n🔗 Sizning Referal ssilka: \nhttp://t.me/uzcoinphpbot?start=$chatID \n➖➖➖➖➖➖➖➖➖➖ \n\n❗️Ushbu ssilkani do'stlaringizga tarqating Agar ular shu ssilkani bosib botga kirsa va kanalga a'zo bo'lsa sizga Referral qo'shiladi";
    sendMessage($text);
}

function showAboutBot(){
    global $chatID, $db;

    $botLiveDay = abs(round((time() - strtotime('23 March 2022')) / 86400));
    $stat = $db->botStatistics()[0];
    $usersCount = $stat['count'];
    $todayUsersCount = $stat['todayCount'];
    $countReferrals = $stat['referrals'];

    $text = "📊 Bizning bot statistikasi: \n\n🕜 Ishlagan kunlari: $botLiveDay kun \n👨 Jami foydalanuvchilar: $usersCount ta \n😺 Bugun qo'shildi: $todayUsersCount ta \n👥 Jami Referallar: $countReferrals ta";
    sendMessage($text);
}

function showGames(){
    global $telegram, $chatID;

    $gameButtons = $telegram->buildInlineKeyBoard([
        [$telegram->buildInlineKeyboardButton("🍀 Lotereya 🍀", '', 'lotereya')],
        [$telegram->buildInlineKeyboardButton("🎲 Random 🎲", '', 'random')]
    ]);

    $text = "*🎲 O'yinlardan birini tanlang*";
    sendMessage($text, true, $gameButtons);
}

function startRandomGame(){
    global $telegram, $chatID, $messageID;

    $gameButtons = $telegram->buildInlineKeyBoard([
        [$telegram->buildInlineKeyboardButton("🎲 Boshlash 🎲", '', 'randomGame')]
    ]);

    $telegram->editMessageText(['message_id' => $messageID, 'chat_id' => $chatID, 'text' => "O'yinda qatnashish narxi 200 so'm💳 Qatnashish uchun Hisobingizda kamida 200 som bo'lishi kerak\. *🎲 Boshlash 🎲 tugmasini bosing📍 va 5000 🎁 somgacha pul miqdorini yutib oling❗️*", 'reply_markup' => $gameButtons, 'parse_mode' => 'markdownV2']);
}
function randomGame(){
    global $telegram, $chatID, $db;
    $user = $db->getUser($chatID);
    $currentBalance = $user['balance'];

    if ($currentBalance >= 200) {
        $randNumber = rand(0, 5000);
        $balance = ($currentBalance - 200) + $randNumber;
        $db->editBalance($chatID, $balance);
        
        $telegram->answerCallbackQuery(['callback_query_id' => $telegram->Callback_ID(), 'text' => "Tabriklaymiz Siz $randNumber so'm yutib oldingiz!", 'show_alert' => true]);
    } else {
        sendMessage("Balansda mablag' yetarli emas! Sizning balansingiz $currentBalance so'm");
    }
}

function sendMessage($text, $parseMode = false, $replyMarkup = null){
    global $telegram, $chatID;

    if ($replyMarkup == null && !$parseMode) {
        $telegram->sendMessage(['chat_id' => $chatID, 'text' => $text]);
    } else if ($replyMarkup != null && !$parseMode) {
        $telegram->sendMessage(['chat_id' => $chatID, 'text' => $text, 'reply_markup' => $replyMarkup]);
    } else if ($replyMarkup == null && $parseMode) {
        $telegram->sendMessage(['chat_id' => $chatID, 'text' => $text, 'parse_mode' => 'markdownV2']);
    } else {
        $telegram->sendMessage(['chat_id' => $chatID, 'text' => $text, 'reply_markup' => $replyMarkup, 'parse_mode' => 'markdownV2']);
    }
}

function resendMessage(){
    global $telegram;

    sendMessage(json_encode($telegram->getData(), JSON_PRETTY_PRINT));
}
// var_dump(date('Y.m.d H:i', strtotime('00:00')));
?>