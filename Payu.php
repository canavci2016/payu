<?php

class Payu
{
    private $ch;
    private $url = "https://secure.payu.com.tr/order/alu/v3";
    private $binURL = 'https://secure.payu.com.tr/api/card-info/v1/';
    private $tokenURL = 'https://secure.payu.com.tr/order/tokens/';
    private $merchantID;
    private $secretKey;
    private $products = [];
    private $order = [
        'PRICES_CURRENCY' => 'TRY',
        'PAY_METHOD' => 'CCVISAMC',
        'LU_ENABLE_TOKEN' => '1',
        'LU_TOKEN_TYPE' => 'PAY_BY_CLICK',
        'CLIENT_IP' => '127.0.0.1',
    ];


    /**
     * Payu constructor.
     * @param $ch
     */
    public function __construct($merchantID, $secretKey, $url = null)
    {
        $this->ch = CURL::getInstance();

        $this->merchantID = $merchantID;
        $this->secretKey = $secretKey;

        if (!is_null($url))
            $this->setUrl($url);
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


    /*
    * 3d security geri dönüş url
    * */
    public function setBackRef($url)
    {
        $this->order['BACK_REF'] = $url;

    }

    /*
    * ip adresi
    * */
    public function setClientIp($ip)
    {
        $this->order['CLIENT_IP'] = $ip;

    }

    /*
    * iban sorgulama
    *
    * */

    public function binSearch($cartNumber)
    {
        $TIMESTAMP = time();
        $sig = hash_hmac('sha256', $this->merchantID . $TIMESTAMP, $this->secretKey);
        $urlReqest = $this->binURL . $cartNumber . "?merchant=" . $this->merchantID . "&timestamp=" . $TIMESTAMP . "&signature=" . $sig;
        try {
            $json = file_get_contents($urlReqest);
        } catch (\Exception $e) {
            $json = json_encode([]);
        }

        return json_decode($json, true);

    }


    /*
    * token kullanarak satış yapmak
    * */
    public function saleUsingToken($token = null)
    {

        $order['AMOUNT'] = '100';
        $order['CURRENCY'] = 'TRY';
        $order['EXTERNAL_REF'] = time() . rand(0, 9999);
        $order['MERCHANT'] = $this->merchantID;
        $order['METHOD'] = 'TOKEN_NEWSALE';
        $order['REF_NO'] = time();
        $order['TIMESTAMP'] = $this->getTimestamp("YmdHis");


        $order['SIGN'] = $this->calculateSignature($order);

        $this->ch->setUrl($this->tokenURL);
        $res = $this->ch->execute($order, true);
        print_r($res);


    }

    /*
    * token geçmişi
    * */
    public function readTokenPaymentDetail($token = null)
    {

        $order['MERCHANT'] = $this->merchantID;
        $order['METHOD'] = 'TOKEN_GETINFO';
        $order['REF_NO'] = time();
        $order['TIMESTAMP'] = $this->getTimestamp("YmdHis");


        $order['SIGN'] = $this->calculateSignature($order);


        $this->ch->setUrl($this->tokenURL);
        $res = $this->ch->execute($order, true);
        print_r($res);


    }

    /*
    * token iptali
    * */
    public function cancelTokenPayment($token = null)
    {

        $order['MERCHANT'] = $this->merchantID;
        $order['METHOD'] = 'TOKEN_CANCEL';
        $order['REF_NO'] = time();
        $order['TIMESTAMP'] = $this->getTimestamp("YmdHis");

        $order['SIGN'] = $this->calculateSignature($order);

        $this->ch->setUrl($this->tokenURL);
        $res = $this->ch->execute($order, true);
        print_r($res);


    }

    private function getTimestamp($format = 'Y-m-d H:i:s')
    {
        return date($format, time() - date("Z"));
    }


    /*
    * teslimat bilgilerini doldurur.
    * */
    public function setDelivery($firstName, $lastName, $phone, $address, $zip, $city, $state, $country_code = 'TR')
    {
        $this->order['DELIVERY_FNAME'] = $firstName;
        $this->order['DELIVERY_LNAME'] = $lastName;
        $this->order['DELIVERY_PHONE'] = $phone;
        $this->order['DELIVERY_ADDRESS'] = $address;
        $this->order['DELIVERY_ZIPCODE'] = $zip;
        $this->order['DELIVERY_CITY'] = $city;
        $this->order['DELIVERY_STATE'] = $state;
        $this->order['DELIVERY_COUNTRYCODE'] = $country_code;
    }


    /*
    * ödeme yapan kişinin bilgileri
    *
    * */
    public function setBill($name, $lastname, $email, $phone, $country_code = 'TR')
    {
        $this->order['BILL_LNAME'] = $lastname;
        $this->order['BILL_FNAME'] = $name;
        $this->order['BILL_EMAIL'] = $email;
        $this->order['BILL_PHONE'] = $phone;
        $this->order['BILL_COUNTRYCODE'] = $country_code;
    }

    /*
    * Kredi kartı bilgilerini doldurur
    *
    * */
    public function setCreditCard($number, $exp_month, $exp_year, $cvv, $owner, $installments = '1')
    {
        $this->order['SELECTED_INSTALLMENTS_NUMBER'] = $installments;
        $this->order['CC_NUMBER'] = $number;
        $this->order['EXP_MONTH'] = $exp_month;
        $this->order['EXP_YEAR'] = $exp_year;
        $this->order['CC_CVV'] = $cvv;
        $this->order['CC_OWNER'] = $owner;
    }


    public function setProduct($name, $code, $info, $price, $qty)
    {
        $this->products[] = [
            'name' => $name,
            'code' => $code,
            'info' => $info,
            'price' => $price,
            'qty' => $qty,
        ];


    }

    /*
    * ödeme yap
    * */
    public function authorize($orderId, $redirectUrl)
    {
        $this->setBackRef($redirectUrl);

        $this->order['MERCHANT'] = $this->merchantID;
        $this->order['ORDER_REF'] = $orderId;
        $this->order['ORDER_DATE'] = $this->getTimestamp();
        $this->productOptimizer($this->order);
        $this->order["ORDER_HASH"] = $this->calculateSignature($this->order);

        $this->ch->setUrl($this->url);
        try {
            $response = $this->ch->execute($this->order, true);

        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $this->responseParser($response);
    }


    private function responseParser($response)
    {

        $xml = $this->xmlToArray($response);
        $res = [
            'Status' => 'Error',
            'Message' => 'xml not found',
            'url' => null,
            'Detail' => null,
        ];

        if (isset($xml['STATUS']) && isset($xml['RETURN_CODE'])) {
            $res['Message'] = $xml['RETURN_MESSAGE'];
            $res['Detail'] = $xml;

            switch ($xml['STATUS']):

                case 'SUCCESS':

                    switch ($xml['RETURN_CODE']) :
                        case 'AUTHORIZED':   //non 3d payment is success
                            $res['Status'] = 'Success';

                            break;

                        case  '3DS_ENROLLED':
                            $res['Status'] = 'Wait';
                            $res['url'] = $xml['URL_3DS'];
                            break;

                        case  'PENDING_AUTHORIZATION':
                            $res['Status'] = 'Wait';
                            $res['url'] = $xml['URL_REDIRECT'];
                            break;


                            break;


                    endswitch;

                    break;


            endswitch;


        }
        return $res;

    }


    private function xmlToArray($response)
    {
        $xml = simplexml_load_string($response) or die("Error: Cannot create object");
        $xml = json_encode($xml);
        $xml = json_decode($xml, true);
        return $xml;
    }


    /*
    * ürünleri sıralamaya sokar
    * */
    private function productOptimizer(&$order)
    {
        $productArray = array();
        foreach ($this->products as $i => $product) {

            $newProduct = array(

                "ORDER_PNAME[" . $i . "]" => $product['name'],
                "ORDER_PCODE[" . $i . "]" => $product['code'],
                "ORDER_PINFO[" . $i . "]" => $product['info'],
                "ORDER_PRICE[" . $i . "]" => $product['price'],
                "ORDER_QTY[" . $i . "]" => $product['qty'],

            );
            array_push($productArray, $newProduct);

        }

        foreach ($productArray as $product) {
            foreach ($product as $key => $value) {
                $order[$key] = $value;
            }
        }

        $this->products = [];
    }


    /*
    * imzayı hasliyor
    * */
    private function calculateSignature(&$params)
    {
        ksort($params);
        $hashString = '';

        foreach ($params as $v) {
            $hashString .= strlen($v) . $v;
        }

        return hash_hmac('md5', $hashString, $this->secretKey);
    }


}