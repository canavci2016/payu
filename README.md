# payu
Site içi ödeme yapmanızı sağlar api v3 yani son versiyondur kullanımına..
test.php den bakabilirsiniz. test.php de ki directiveleri uygularsanız işlem tamamdır. authorize fonksiyon çalıştırınca 
aşağıdaki responseları alacaksınız ve açıklamalarını ekledim . (NOT: Şuanda bu yapıyı 2-3 sitede şuanda aktif olarak kullanmaktayım.)

Status=>'Wait' durumu 3d ödeme demektir.
3D ÖDEME yapıldığında aşağıdakine benzer bir response donecektir.
verilen url adresi sizin kullanıcıyı yonlendireceğiniz  adrestir.

Array
(
    [Status] => Wait
    [Message] => 3DS Enrolled Card.
    [url] => https://secure.payu.com.tr/order/3ds/begin/refno/35069526/sign/39eb355080ea591137aefb170e48a2323/
    [Detail] => Array
        (
            [REFNO] => 35069526
            [ALIAS] => 5a72186b4b0b1562e5190317e6d5f444
            [STATUS] => SUCCESS
            [RETURN_CODE] => 3DS_ENROLLED
            [RETURN_MESSAGE] => 3DS Enrolled Card.
            [DATE] => 2017-06-22 10:46:07
            [URL_3DS] => https://secure.payu.com.tr/order/3ds/begin/refno/35069526/sign/39eb35508230ea591137aefb170e48a2332a/
            [HASH] => d057e6843514e3271accf361fd5f856d
        )


Status=>'Success' ödeme gerçekleşti kullanıcıdan ödemeyi aldınız.
Status=>'Error' ise hata vardır. hata mesahjına Message kısmı veya Detail=> RETURN_MESSAGE bölümünden ulaşabilirsiniz.

------------------- KARTLARIN TAKSİT ORANLARINI SORGULAMA --------------------

installments() isimli fonksiyonu ile  taksit oranları gelir
    [value] => Array
        (
            [axess] => Array
                (
                    [2] => Array
                        (
                            [percent] => 4.22
                            [total] => 0.00
                            [rate] => 0.00
                        )

                    [3] => Array
                        (
                            [percent] => 5.04
                            [total] => 0.00
                            [rate] => 0.00
                        )

                    [4] => Array
                        (
                            [percent] => 5.60
                            [total] => 0.00
                            [rate] => 0.00
                        )

                )

            [bonus] => Array
                (
                    [2] => Array
                        (
                            [percent] => 4.22
                            [total] => 0.00
                            [rate] => 0.00
                        )

                    [3] => Array
                        (
                            [percent] => 5.04
                            [total] => 0.00
                            [rate] => 0.00
                        )

                    [4] => Array
                        (
                            [percent] => 5.60
                            [total] => 0.00
                            [rate] => 0.00
                        )

             )
                )


---------------------- RAPOR SERVİSLERİ-------------

orderReports($startDate, $endDate)  //YYYY-mm-dd
productReports($startDate, $endDate)  //YYYY-mm-dd
operatorReports($startDate, $endDate)  //YYYY-mm-dd