# PayPal Adaptive Payments Wrapper

A simple wrapper around the PayPal Adaptive Payments API.

Used in [this sample](https://github.com/braintreedev/sample-12-paypal-adaptive-payments-chained-parallel-php).

## Basic usage

## Configuration

```php
require('adaptive-payments.php'); # change to correct path if needed

$config = array(
  "environment" => "sandbox", # or live
  "userid" => "info-facilitator_api1.commercefactory.org",
  "password" => "1399139964",
  "signature" => "AFcWxV21C7fd0v3bYYYRCpSSRl31ABA-4mmfZiu.G30Dl3DKyBo9-GF8",
  // "appid" => "", # You can set this when you go live
);

$paypal = new PayPal($config);
```

## Setup payment

This shows how to use the `Pay` command (more [details here](https://developer.paypal.com/docs/classic/api/adaptive-payments/Pay_API_Operation/)).

You can make any call as specified in the [API specification](https://developer.paypal.com/docs/classic/api/#ap) with this method.

```php
$result = $paypal->call(
  array(
    'actionType'  => 'PAY',
    'currencyCode'  => 'USD',
    'feesPayer'  => 'EACHRECEIVER',
    'memo'  => 'Order number #123',
    'cancelUrl' => 'cancel.php',
    'returnUrl' => 'success.php',
    'receiverList' => array(
      'receiver' => array(
        array(
          'amount'  => '100.00',
          'email'  => 'info-facilitator@commercefactory.org',
          'primary'  => 'true',
        ),
        array(
          'amount'  => '45.00',
          'email'  => 'us-provider@commercefactory.org',
        ),
        array(
          'amount'  => '45.00',
          'email'  => 'us-provider2@commercefactory.org',
        ),
      ),
    ),
  ), 'Pay'
);
```

In other words:

```php
$result = $paypal->call(
  array_of_options, 'SomeCommand'
);
```

## Redirecting the user

Most API calls are made with `call` but for convenience there's a simple way to build the correct redirec URL:

```php
$paypal->redirect($result);
```

## Finalising the payment

We can again just use the `call` method to make a `PaymentDetails` command:

```php
$paypal->call(
  array(
    'actionType'  => 'Pay',
    'payKey'  => 'some pay key',
  ), "PaymentDetails"
);
```




