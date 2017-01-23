# Pay System DLE-Billing v.0.6
Плагины платежных агрегаторов и систем а так же модифицированный файл plugins.php для DLE-Billing<br>
офф.сайт - <a href="http://dle-billing.ru/">DLE-Billing</a> репозитарий на - <a href="https://github.com/mr-Evgen/dle-billing-module">GitHub</a><br><br>
В связи с тем что есть плагины но по не понятным мне причинам платежные агригаторы не занимаются их поддержкой в актуальном состоянии решил этот пробел исправить и выкладываю в паблик может комуто и понадобятся<br><br>
Free-Kassa - http://free-kassa.ru/ - ver.1.0<br>
OnPay - http://onpay.ru/ - ver.1.0<br>
MyKassa - http://mykassa.org/ - ver.1.0<br><br>
Z-payment - делал для себя сам и т.к. данный плагин есть у разработчика не выкладываю в публичный доступ.<br>
Яндекс.Деньги, PayPal - Преобритал чтобы поддержать разработчиков, связи с тем что уважаю чужой труд я не выкладываю в публичный доступ.<br><br>
Модифицированный файл plugins.php включенны в список не официальные плагины для DLE-Billing v0.6<br>
В модифицированный файл вошли платежные агрегаторы которые официально у себя на сайте выложили плагины или те плагины которых нет у разработчика DLE-Billing на сайте а так же те плагины которые я модифицировал для данного модуля:<br>
Плагины присутствующие на сайте платежного агрегатора совместимые с DLE-Billing v0.6<br>
<a href="https://payanyway.ru/info/w/ru/public/w/partnership/developers/instructions/dle.html">PayAnyWay</a>, <a href="https://payeer.com/ru/modules/">PAYEER</a>, <a href="https://megakassa.ru/cms/">МегаКасса</a><br>
Плагины присутствующие на сайте платежного агрегатора не совместимые с DLE-Billing v0.6<br>
<a href="http://www.mykassa.org/page/cmsmodule">MyKassa</a>, <a href="http://www.free-kassa.ru/news.php?id=146">Free-Kassa</a>, <a href="http://onpaysolutions.ru/%D0%BC%D0%BE%D0%B4%D1%83%D0%BB%D1%8C-%D0%B4%D0%BB%D1%8F-dle-%D0%B1%D0%B0%D0%BB%D0%B0%D0%BD%D1%81-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F/">OnPay</a><br><br>
<img src="https://1.downloader.disk.yandex.ru/disk/78ab49eda514c32e1846fbc4510072b9c13aab46e9b02bd737963fdba66489a1/58863fbb/OwXzhE8w4G9nYnlcEyYiTz-DTWE-0Y9w4jVa5f1Mi6OA3hd_bEhQK_Vc0f2SM17FccZ3MutcEyuyUCeRaSZ1jw%3D%3D?uid=0&filename=paysis.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&fsize=109726&hid=42e2ddae1f72a91e70677062db71e547&media_type=image&tknv=v2&etag=010f61b30deed34eb55ea9b71e961976"><br><br>
P.S. просьба при копировании матерьяла или выкладывании файлов указывать источник и для <b>САМЫХ УМНЫХ сразу же придупреждаю если увижу хоть на одном ресурсе свои модификации без ссылки на источник больше модифицировать файлы не буду или модификация будет за деньги<br><center>Уважайте чужой труд!!!</center></b>
# Установка
Папки PayAnyWay, Payeer, Megakassa, FreeKassa, MyKassa, OnPay расположить в директории /engine/modules/billing/paysys/<br><br>
<img src="https://4.downloader.disk.yandex.ru/disk/db11deb43d85a3e8c890ca27a1e7c505971e1a997829864676ccf39f5efb55f4/588645b4/OwXzhE8w4G9nYnlcEyYiT5anBiAckuJphApF9iZVyriEpByD10QDJz-mAxHWFNL2vxSGlzlG5jJ3myhuapsFIA%3D%3D?uid=0&filename=ftpbil.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&fsize=54615&hid=5e32f62e5fc30ae27b2b1377c57a3593&media_type=image&tknv=v2&etag=8f2bb72e40137edef6545841c3ad345e"><br>
Что бы использовать модифицированный файл plugins.php необходимо заменить строку<br>'url_catalog' => "http://dle-billing.ru/engine/ajax/extras/plugins.php",<br>в файле config.php расположенный в деректории /engine/data/billing/ на 
