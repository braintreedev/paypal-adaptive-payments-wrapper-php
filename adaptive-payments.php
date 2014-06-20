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

  public function call($options = [], $method) {
    $this->prepare($options);
    return $this->_curl($this->api_url($method), $options, $this->headers($this->config));
  }

  public function redirect($response) {
    if(@$response["payKey"]) $redirect_url = sprintf("%s?cmd=_ap-payment&paykey=%s", $this->redirect_url(), $response["payKey"]);
    else $redirect_url = sprintf("%s?cmd=_ap-preapproval&preapprovalkey=%s", $this->redirect_url(), $response["preapprovalKey"]);

    header("Location: $redirect_url");
  }

  private function redirect_url() {
    return $this->urls[$this->config["environment"]]["redirect"];
  }

  private function api_url($method) {
    return $this->urls[$this->config["environment"]]["api"].$method;
  }

  private function headers($config){
    $header = array(
      "X-PAYPAL-SECURITY-USERID: ".$config['userid'],
      "X-PAYPAL-SECURITY-PASSWORD: ".$config['password'],
      "X-PAYPAL-SECURITY-SIGNATURE: ".$config['signature'],
      "X-PAYPAL-REQUEST-DATA-FORMAT: JSON",
      "X-PAYPAL-RESPONSE-DATA-FORMAT: JSON",
    );

    if(array_key_exists('appid', $config) && !empty($config['appid']))
      $header[] = "X-PAYPAL-APPLICATION-ID: ".$config['appid'];
    else
      $header[] = "X-PAYPAL-APPLICATION-ID: APP-80W284485P519543T";

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

  private function prepare(&$options) {
    $this->expand_urls($options);
    $this->merge_defaults($options);
  }

  private function expand_urls(&$options) {
    $regex = '#^https?://#i';
    if(array_key_exists('returnUrl', $options) && !preg_match($regex, $options['returnUrl'])) {
      $this->expand_url($options['returnUrl']);
    }

    if(array_key_exists('cancelUrl', $options) && !preg_match($regex, $options['cancelUrl'])) {
      $this->expand_url($options['cancelUrl']);
    }
  }

  private function expand_url(&$url) {
    $current_host = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}";
    if(preg_match("#^/#i", $url)) {
      $url = $current_host.$url;
    }
    else {
      $directory = dirname($_SERVER['PHP_SELF']);
      $url = $current_host.$directory.$url;
    }
  }

  private function merge_defaults(&$options) {
    $defaults = array(
      'requestEnvelope' => array(
        'errorLanguage' => 'en_US',
      )
    );

    $options = array_merge($defaults, $options);
  }
}
