<?php
/***********************************************************************/
// Monglian language pack
// contributed by: infsol
/***********************************************************************/
/* Please submit corrections to info@phplivesupport.com - Thank you! */

// IMPORTANT: in the PHP Live! setup area where you create departments, you should also
// edit the "Chat Greeting", "Offline Message" and "Transcript Email" to your language

$LANG = Array() ;
// do not attempt to modify the CHARSET unless the characters are not displaying properly
$LANG["CHARSET"] = "UTF-8" ;

/* visitor chat window */
$LANG["CHAT_WELCOME"] = "Тавтай морилно уу!" ;
$LANG["CHAT_WELCOME_SUBTEXT"] = "Та дараах мэдээллийг бөглөж өгнө үү." ;
$LANG["CHAT_SELECT_DEPT"] = "--- Хэлтэс сонгоно уу ---" ;
$LANG["CHAT_BTN_START_CHAT"] = "Чатлах /Chat/" ;
$LANG["CHAT_BTN_EMAIL"] = "Имэйл илгээх /Send/" ;
$LANG["CHAT_BTN_EMAIL_TRANS"] = "Чатын түүх илгээх" ;
$LANG["CHAT_PRINT"] = "Чатын түүх хэвлэх" ;
$LANG["CHAT_CHAT_WITH"] = "таны чатлаж байгаа хүн:" ;
$LANG["CHAT_SURVEY_THANK"] = "Таны санал хүсэлт амжилттай илгээгдлээ. Танд баярлалаа! " ;
$LANG["CHAT_CLOSE"] = "Цонхыг хаах" ;
$LANG["CHAT_SOUND"] = "Дууны тохиргоо" ;
$LANG["CHAT_TRANSFER"] = "Чатыг дараах хүн рүү шилжүүллээ:" ;
$LANG["CHAT_TRANSFER_TIMEOUT"] = "Таны чатыг одоогоор өөр хүн рүү шилжүүлэх боломжгүй байна. Өмнөх хүн рүү буцаан шилжүүлж байна…" ;


/* leave a message area */
$LANG["MSG_LEAVE_MESSAGE"] = "Та мессежээ энд бичнэ үү" ;
$LANG["MSG_EMAIL_FOOTER"] = " " ;
$LANG["MSG_PROCESSING"] = "Өмнөх зурвасыг боловсруулж байна. Түр хүлээгээд дахин хүсэлт илгээнэ үү." ;


/* internal text */
$LANG["TRANSCRIPT_SUBJECT"] = "Тантай чатлаж байгаа хүн" ;


/* chat notifications */
$LANG["CHAT_NOTIFY_JOINED"] = "Чатанд холбогдлоо." ;
$LANG["CHAT_NOTIFY_RATE"] = "Танд үйлчилсэн операторт үнэлгээ өгнө үү?" ;
$LANG["CHAT_NOTIFY_DISCONNECT"] = "Сүлжээ тасарсны улмаас чат дууслаа. " ;
$LANG["CHAT_NOTIFY_VDISCONNECT"] = "Харилцагч чатаас гарлаа. Чат дууслаа." ;
$LANG["CHAT_NOTIFY_ODISCONNECT"] = "Оператор чатаас гарлаа. Чат дууслаа." ;
$LANG["CHAT_NOTIFY_LOOKING_FOR_OP"] = "%%visitor%% танд энэ өдөрийн мэнд хүргэе.Оператор тун удахгүй тантай холбогдох болно. Танд баярлалаа." ;
$LANG["CHAT_NOTIFY_OP_NOT_FOUND"] = "Уучлаарай. Одоогоор таньтай шууд холбогдох оператор байхгүй байна. Та И-мэйл илгээж болно. Танд баярлалаа." ;
$LANG["CHAT_NOTIFY_IDLE_TITLE"] = "Чат идэвхи болж байна. Хариу илгээнэ үү" ;
$LANG["CHAT_NOTIFY_IDLE_AUTO_DISCONNECT"] = "Чат холболт хаагдлаа" ;


/* javascript alerts */
$LANG["CHAT_JS_BLANK_DEPT"] = "Хэлтэсээ сонгоно уу." ;
$LANG["CHAT_JS_BLANK_EMAIL"] = "Та өөрийн имэйл хаягаа оруулна уу." ;
$LANG["CHAT_JS_INVALID_EMAIL"] = " Та зөв имэйл хаяг оруулна уу. (example: name@domain.com)" ;
$LANG["CHAT_JS_LEAVE_MSG"] = "Ариг банк: мессеж оруулна уу" ;
$LANG["CHAT_JS_EMAIL_SENT"] = "Aмжилттай илгээгдлээ!" ;
$LANG["CHAT_JS_CHAT_EXIT"] = "Бид нартай холбогдсонд баярлалаа. Та өдрийг сайхан өнгөрүүлээрэй. " ;
$LANG["CHAT_JS_CUSTOM_BLANK"] = "Шаардлагатай бүх талбарыг оруулна уу." ;


/* words */
$LANG["TXT_DEPARTMENT"] = "Хэлтэс /Department/" ;
$LANG["TXT_ONLINE"] = "Онлайн /Online/" ;
$LANG["TXT_OFFLINE"] = "Опплайн /Offline/" ;
$LANG["TXT_NAME"] = "Нэр /Name/" ;
$LANG["TXT_EMAIL"] = "И-мэйл /E-mail/" ;
$LANG["TXT_QUESTION"] = "Асуулт /Question/" ;
$LANG["TXT_CONNECT"] = "Холбогдох" ;
$LANG["TXT_CONNECTING"] = "Холбогдож байна…" ;
$LANG["TXT_SUBMIT"] = "Илгээх /Send/" ;
$LANG["TXT_DISCONNECT"] = "Холболт таслах" ;
$LANG["TXT_SUBJECT"] = "Гарчиг /Subject/" ;
$LANG["TXT_MESSAGE"] = "Мессеж /Message/" ;
$LANG["TXT_LIVECHAT"] = "Ариг Банк" ;
$LANG["TXT_OPTIONAL"] = "заавал шаардлагагүй" ;
$LANG["TXT_TYPING"] = "бичиж байна..." ;
$LANG["TXT_SECONDS"] = "seconds" ;


/* as of v.4.5.9, all new lang vars will be included here in sequential order */
$LANG["CHAT_COMMENT_THANK"] = "Сэтгэгдэл илгээгдлээ. Танд баярлалаа." ;
$LANG["CHAT_JS_BLANK_COMMENT"] = "Тайлбар өгнө үү." ;
$LANG["CHAT_ERROR_DC"] = "Холболтын алдаа гарлаа.  Хуудсыг дахин дуудаж үзнэ үү." ;
$LANG["TXT_COMMENT"] = "Сэтгэгдэл" ;
$LANG["TXT_UPLOAD_FILE"] = "Файл илгээх" ;
$LANG["TXT_UPLOAD_SEND"] = "Илгээж байна..." ;
$LANG["TXT_RATING"] = "Үнэлгээ" ;
$LANG["CHAT_INVITE_DECLINED"] = "Зочин чатын урилгыг татгалзлаа." ;
$LANG["TXT_DOWNLOAD"] = "Татах" ;
?>