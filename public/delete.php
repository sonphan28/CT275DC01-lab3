<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275DC01_lab3\Contact;

$contact = new Contact($PDO);

// Lấy ID từ request (chấp nhận cả GET và POST)
$id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) : false;

// Nếu không có ID hợp lệ hoặc không tìm thấy liên hệ thì quay về trang chủ
if (!$id || !$contact->find($id)) {
    redirect('/');
    exit;
}

// Xử lý khi người dùng xác nhận XÓA (Phương thức POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($contact->delete()) {
        $_SESSION['flash_message'] = 'Contact deleted successfully!';
    }
    redirect('/');
    exit;
}

// Nếu là phương thức GET: Hiển thị giao diện XÁC NHẬN XÓA
include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
    <?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

    <div class="container mt-4">
        <?php
        $subtitle = 'Confirm deletion';
        include_once __DIR__ . '/../src/partials/heading.php';
        ?>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card border-danger mb-3">
                    <div class="card-header bg-danger text-white fw-bold">
                        Xác nhận xóa liên hệ
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Bạn có chắc chắn muốn xóa liên hệ <strong><?= html_escape($contact->name) ?></strong> (SĐT: <?= html_escape($contact->phone) ?>) không?
                        </p>
                        <p class="text-muted small">* This action cannot be undone.</p>

                        <form method="post" action="">
                            <input type="hidden" name="id" value="<?= $contact->id ?>">

                            <button type="submit" class="btn btn-danger">
                                Đồng ý xóa
                            </button>
                            <a href="/" class="btn btn-secondary">
                                Hủy bỏ
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>
</body>

</html>