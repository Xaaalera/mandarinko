<?php


class NewPay
{
    public $merchantid;
    public $secret;
    public $base_url;

    function __construct($merchantid = "216",
                         $secret = "123321",
                         $base_url = "https://secure.mandarinpay.com/"
    )

    {
        $this->merchantid = $merchantid;
        $this->secret = $secret;
        $this->base_url = $base_url;

    }

    private function to_array_costumerinfo($costumerinfo)
    {
        $array["customerInfo"] = array("email" => $costumerinfo->email,
            "phone" => $costumerinfo->phone);
        return ($array);
    }

    public function calc_sign($fields) //sign  need to generate form   Pay
    {
        ksort($fields);
        $secret_t = '';
        foreach ($fields as $key => $val) {
            $secret_t = $secret_t . '-' . $val;
        }

        $secret_t = substr($secret_t, 1) . '-' . $this->secret;
        return hash("sha256", $secret_t);
    }


    public function generate_form($orderid, $price, $customer_mail) //generate form  to pay
    {
        $form = "";
        $array_form_data["orderId"] = $orderid;
        $array_form_data["price"] = $price;
        $array_form_data["customer_email"] = $customer_mail;
        $array_form_data["merchantId"] = $this->merchantid;
        $sign = $this->calc_sign($array_form_data);
        $form = $form . "<form action=\"{$this->base_url}Pay\" method=\"POST\"> ";
        foreach ($array_form_data as $key => $val) {
            $form = $form . '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($val) . '"/>' . "\n";
        }
        $form = $form . '<input type="hidden" name="sign" value="' . $sign . '"/>';
        $form = $form . "<input type=\"submit\" id=\"button_send_form\" value=\"Оплатить\" />";
        $form = $form . "</form>";
        return $form;
    }

    private function reqid_calc()
    {    //this is calk reqid this is function need to registr Aut
        $reqid = time() . "_" . microtime(true) . "_" . rand();
        return $reqid;
    }

    private function gen_auth()
    {
        $reqid = $this->reqid_calc();
        $hash = hash("sha256", $this->merchantid . "-" . $reqid . "-" . $this->secret);
        return $this->merchantid . "-" . $hash . "-" . $reqid; //this is  "merchantId-SHA256(merchantId-requestId-secret)-requestId"
    }


    private function gen_payment($orderid, $price)
    { //generate  array payment
        $array["payment"] = array("orderId" => $orderid,
            "action" => "pay",
            "price" => $price);
        return $array;
    }

    public function pay_interactive($orderid, $price, $costumerinfo, $customvalues = array())
    {
        $payment = $this->gen_payment($orderid, $price);
        $costumerinfo = $this->to_array_costumerinfo($costumerinfo);
        $array_content = array_merge($payment, $costumerinfo);
        $array_content["customValues"] = $customvalues;
        $json_content = json_encode($array_content);
        $url_transaction = $this->base_url . "api/transactions";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        return $result;

    }

        private function gen_preauth($orderid, $price)
    { //generate  array payment
        $array["payment"] = array("orderId" => $orderid,
            "action" => "preauth",
            "price" => $price);
        return $array;
    }

        public function pay_preauth($orderid, $price,$costumerinfo)
    {
        $payment = $this->gen_preauth($orderid, $price);
        $costumerinfo = $this->to_array_costumerinfo($costumerinfo);
        $array_content = array_merge($payment, $costumerinfo);
              // $payment["target"]=array('card'=>'0eb51e74-e704-4c36-b5cb-8f0227621518');
        $json_content = json_encode($array_content);
        $url_transaction = $this->base_url . "api/transactions";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        print_r($result);
        return $result;

    }


    private function gen_payout($orderid, $price)
    { //generate  array payout
        $array["payment"] = array("orderId" => $orderid,
            "action" => "payout",
            "price" => $price);
        return $array;
    }

    public function payout_interactive($orderid, $price, $costumerinfo, $customvalues = array())
    {
        $payment = $this->gen_payout($orderid, $price);
        $costumerinfo = $this->to_array_costumerinfo($costumerinfo);
        $array_content = array_merge($payment, $costumerinfo);
        $array_content["customValues"] = $customvalues;
        $json_content = json_encode($array_content);
        $url_transaction = $this->base_url . "api/transactions";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        return $result;

    }


    public function new_card_binding($costumerinfo)
    {
        $costumerinfo = $this->to_array_costumerinfo($costumerinfo);
        $json_content = json_encode($costumerinfo);
        $url_transaction = $this->base_url . "api/card-bindings";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        return $result;
    }


    public function pay_from_card_binding($orderid, $price, $id_card_number)
    {
        $payout = $this->gen_payment($orderid, $price);
        $payout["target"]["card"] = $id_card_number;
        $json_content = json_encode($payout);
        $url_transaction = $this->base_url . "api/transactions";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        return $result;

    }

    public function payout_from_card_binding($orderid, $price, $id_card_number)
    {
        $payout = $this->gen_payment($orderid, $price);
        $payout["payment"]["action"] = "payout";
        $payout["target"]["card"] = $id_card_number;
        $json_content = json_encode($payout);
        $url_transaction = $this->base_url . "api/transactions";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        return $result;

    }


    //generate array  to pay transaction on card

    private function gen_array_know_transaction($payment, $costumerinfo, $knowcardnumber)
    {
        //  $mail=CustomerInfo::$mail;
        $payment["payment"]["action"] = "payout";
        $array = array_merge($payment, $costumerinfo);
        $array["target"]["knownCardNumber"] = $knowcardnumber;
        return ($array);

    }

    public function payout_to_known_card($orderid, $price, $costumerinfo, $knowcardnumber)
    {
        $payout = $this->gen_payment($orderid, $price);
        $costumerinfo = $this->to_array_costumerinfo($costumerinfo);
        $payout = $this->gen_array_know_transaction($payout, $costumerinfo, $knowcardnumber);
        $json_content = json_encode($payout);
        $url_transaction = $this->base_url . "api/transactions";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        return $result;

    }

    private function gen_array_rebill_transaction($rebill_array, $rebill_id)
    {
        $rebill_array["target"]["rebill"] = $rebill_id;
        return ($rebill_array);
    }

    public function rebill_transaction($orderid, $price, $rebill_id)
    {
        $rebill_array = $this->gen_payment($orderid, $price);
        $rebill_array = $this->gen_array_rebill_transaction($rebill_array, $rebill_id);
        $json_content = json_encode($rebill_array);
        $url_transaction = $this->base_url . "api/transactions";
        $ch = curl_init($url_transaction);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth:" . $this->gen_auth(),
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch))
            throw new Exception(curl_error($ch));
        $result = json_decode($result);
        return $result;


    }

    public function check_sign($req)
    {
        $sign = $req['sign'];
        unset($req['sign']);
        $to_hash = '';
        if (!is_null($req) && is_array($req)) {
            ksort($req);
            $to_hash = implode('-', $req);
        }

        $to_hash = $to_hash . '-' . $this->secret;
        $calculated_sign = hash('sha256', $to_hash);
        return $calculated_sign == $sign;
    }


}