<?php
session_start();

$app_id = 'YOUR_APP_ID';
$app_secret = 'YOUR_APP_SECRET';

// Kiểm tra state để chống CSRF
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die("State không hợp lệ!");
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Lấy access token
    $token_url = "https://oauth.zaloapp.com/v4/access_token?app_id=$app_id&app_secret=$app_secret&code=$code&grant_type=authorization_code";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];

        // Lấy thông tin user
        $user_url = "https://openapi.zalo.me/v2.0/me?access_token=$access_token";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $user_response = curl_exec($ch);
        curl_close($ch);

        $user_info = json_decode($user_response, true);

        echo "<h2>Thông tin User Zalo</h2>";
        echo "<pre>";
        print_r($user_info);
        echo "</pre>";

    } else {
        echo "Lỗi khi lấy access token: " . $response;
    }

} else {
    echo "Chưa có code từ Zalo.";
}
