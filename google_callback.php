<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();
$client->setClientId("1026943602008-7qeiocgt704vnup7tbb7mq3sq0hkalpc.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri("http://localhost/banhangonline/google_callback.php");
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        die('Lỗi khi lấy token: ' . htmlspecialchars($token['error']));
    }

    $client->setAccessToken($token);

    // Namespace mới của Google API
    $oauth2 = new Google\Service\Oauth2($client);
    $userinfo = $oauth2->userinfo->get();

    // Kết nối DB
    $conn = new mysqli("localhost", "root", "", "banhangonline");
    if ($conn->connect_error) {
        die("Kết nối DB thất bại: " . $conn->connect_error);
    }

    // Kiểm tra user đã tồn tại chưa
    $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE email = ?");
    $stmt->bind_param("s", $userinfo->email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu tồn tại -> lấy user
        $user = $result->fetch_assoc();
    } else {
        // Nếu chưa có -> thêm mới
        $stmt = $conn->prepare("INSERT INTO taikhoan (username, email, password, phone, created_at, role, diem) 
                                VALUES (?, ?, ?, ?, NOW(), 'user', 0)");
        $nullPassword = '';
        $nullPhone = '';
        $stmt->bind_param("ssss", $userinfo->name, $userinfo->email, $nullPassword, $nullPhone);
        $stmt->execute();

        $user = [
            'id'       => $stmt->insert_id,
            'username' => $userinfo->name,
            'email'    => $userinfo->email,
            'role'     => 'user',
            'diem'     => 0
        ];
    }

    // ✅ Lưu session giống với đăng nhập thường
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['diem']     = $user['diem'];

    header("Location: index.php");
    exit;
} else {
    echo "Không có mã code từ Google!";
}
