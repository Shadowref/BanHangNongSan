<?php
session_start();

// Sinh OTP ngẫu nhiên 6 số
$otp = rand(100000, 999999);

// Lưu vào session kèm hạn (5 phút)
$_SESSION['otp_code'] = $otp;
$_SESSION['otp_expire'] = time() + 300;

$phone = $_POST['phone'] ?? '';

if(empty($phone)){
    echo json_encode(['status'=>'error','msg'=>'Chưa nhập số điện thoại']);
    exit;
}

// TODO: Tích hợp SMS API để gửi OTP đến số thật
// Ví dụ demo: in OTP ra
// sendSms($phone, "Mã OTP của bạn là: $otp");

echo json_encode(['status'=>'ok','msg'=>'OTP đã gửi! (Demo: '.$otp.')']);
