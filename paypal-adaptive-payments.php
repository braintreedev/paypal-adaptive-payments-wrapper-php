<?php

class PayPal {
  private $config;

  private $urls = array(
    "sandbox" => array(
      "api"      => "https://svcs.sandbox.paypal.com/AdaptivePayments/",
      "redirect" => "https://www.sandbox.paypal.com/webscr",
    ),
    "live" => array(
      "api"      => "https://svcs.paypal.com/AdaptivePayments/",
      "redirect" => "https://www.paypal.com/webscr",
    )
  );

  public function __construct($config) {
    $this->config = $config;
  }

  public function call($options = [], $calltype) {

        if (dirname($_SERVER['PHP_SELF']) == "/") $folder = "";
        else $folder = dirname($_SERVER['PHP_SELF']);

        $options['returnUrl'] = 'http://'.$_SERVER['HTTP_HOST'].$folder.'/success.php?'.strtolower($calltype).'key=${'.$calltype.'key}';
        $options['cancelUrl'] = 'http://'.$_SERVER['HTTP_HOST'].$folder.'/cancel.php';

    return $this->_curl($this->api_url($calltype), $options, $this->headers($this->config));

  }

  public function redirect($response) {

    if(@$response["payKey"]) $redirect_url = sprintf("%s?cmd=_ap-payment&paykey=%s", $this->redirect_url(), $response["payKey"]);
    else $redirect_url = sprintf("%s?cmd=_ap-preapproval&preapprovalkey=%s", $this->redirect_url(), $response["preapprovalKey"]);

    header("Location: $redirect_url");
  }

  private function redirect_url() {
    return $this->urls[$this->config["environment"]]["redirect"];
  }

  private function api_url($calltype) {
    return $this->urls[$this->config["environment"]]["api"].$calltype;
  }

    private function headers($config){

        $header = array(
            "X-PAYPAL-SECURITY-USERID: ".$config['userid'],
            "X-PAYPAL-SECURITY-PASSWORD: ".$config['password'],
            "X-PAYPAL-SECURITY-SIGNATURE: ".$config['signature'],
            "X-PAYPAL-REQUEST-DATA-FORMAT: JSON",
            "X-PAYPAL-RESPONSE-DATA-FORMAT: JSON",
        );

        if($config['appid'] == "") $header[] = "X-PAYPAL-APPLICATION-ID: APP-80W284485P519543T";
        else $header[] = "X-PAYPAL-APPLICATION-ID: ".$config['appid'];

        return $header;
    }

  private function _curl($url, $values, $header) {
    $curl = curl_init($url);

    $options = array(
      CURLOPT_HTTPHEADER      => $header,
      CURLOPT_RETURNTRANSFER  => true,
      CURLOPT_SSL_VERIFYPEER  => false,
      CURLOPT_SSL_VERIFYHOST  => false,
      CURLOPT_POSTFIELDS  => json_encode($values),
      CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_TIMEOUT        => 10
    );

    curl_setopt_array($curl, $options);
    $rep = curl_exec($curl);

    $response = json_decode($rep, true);

    curl_close($curl);

    return $response;

  }
}
