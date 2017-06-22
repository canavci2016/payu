<?php
include 'CURL.php';
include 'Payu.php';

$url = "https://secure.payu.com.tr/order/alu/v3";
$merchantId = 'MERCHANT_ID';  //payu panelinde mevcut
$secretKey = 'SECRET_KEY'; //payu panelinde mevcut
$payu = new Payu($merchantId, $secretKey);

$payu->setBill('CAN', 'AVCI', 'can@crealive.net', '5364553423', 'TR');

$payu->setCreditCard('CART_NUMBER', 'EXP_MONTH', 'EXP_YEAR', 'CVV', "OWNER_NAME_SURNAME", 'TAKSİT_ADETİ');

//$payu->setDelivery('NAME','LAST_NAME','PHONE','ADDRESS','ZİP','CİTY','STATE','COUNTRY_CODE'); //isteğe bağlı

for ($i = 0; $i <= 1; $i++) {
    $payu->setProduct('PROD_NAME', 'PROD_CODE', 'PROD_INFO', 'PRİCE', 'QUANTITY'); //çoklu save eder
}

// 3d ödeme yaptığınızda payu size url gonderecektir sizde kullanıcıyı bu url yonlendireceksiniz
// payunun verdiği linkteki ödeme başarılı veya başarısız sonuçlandığında sizin aşağıda verdiğiniz (REDİRECT_URL_FOR_3D_SECURİTY) urle sonuçlar post ile iletilecektir.

print_r($payu->authorize('ORDER_NUMBER', 'REDİRECT_URL_FOR_3D_SECURİTY'));  //payuya istek atar
