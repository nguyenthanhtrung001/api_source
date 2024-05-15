<?php
// Thực hiện kết nối đến cơ sở dữ liệu MySQL
$servername = "localhost"; // Tên máy chủ MySQL
$username = "api"; // Tên người dùng MySQL
$password = "api"; // Mật khẩu MySQL
$database = "ungdungdidong"; // Tên cơ sở dữ liệu MySQL

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối đến cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Hàm lấy dữ liệu từ bảng mp_user và trả về dưới dạng JSON
function getMpUsers($conn) {
    $sql = "SELECT * FROM mp_users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Duyệt qua các dòng dữ liệu và lưu vào mảng
        $users = array();
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        // Trả về kết quả dưới dạng JSON
        return json_encode($users);
    } else {
        return "Không có dữ liệu";
    }
}

// Gọi hàm để lấy dữ liệu từ bảng mp_user
echo getMpUsers($conn);

// Đóng kết nối
$conn->close();
?>
