<?php
require 'vendor/autoload.php';
$secret_key = $dbConnect->getStripeSecretKey();
\Stripe\Stripe::setApiKey($secret_key);

// ----------------下記はCHatGPTが書いたwebhook--------------

// Webhookの署名検証のためのシークレットキー
$endpoint_secret = 'your-webhook-signing-secret';

// Webhookのペイロードと署名を取得
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

$event = null;

try {
    // 署名の検証
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        $endpoint_secret
    );
} catch (\UnexpectedValueException $e) {
    // 無効なペイロード
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // 無効な署名
    http_response_code(400);
    exit();
}

// イベントタイプの処理
if ($event['type'] == 'checkout.session.completed') {
    $session = $event['data']['object'];

    // 支払いが成功した場合の処理
    // 例：データベースに支払い情報を保存する
    // $session->id などの情報を使用して必要な処理を行う

    // ここでデータベースに保存する処理を行います
    // $session->client_reference_id などを使って顧客情報と紐付ける
}

http_response_code(200);
