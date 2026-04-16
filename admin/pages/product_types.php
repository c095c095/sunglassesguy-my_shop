<?php
include_once "../core/helpers/image_upload.php";

// Get pagination parameters
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($current_page - 1) * $per_page;

// Get search parameter
$search = $_GET['search'] ?? '';

// Build WHERE clause for search
$where_clause = '';
if (!empty($search)) {
    $where_clause = "WHERE pt.name LIKE '%$search%'";
}

// Get total count and paginated results
$result = query("SELECT pt.*, COUNT(p.id) as product_count 
                 FROM product_type pt 
                 LEFT JOIN product p ON pt.id = p.type_id 
                 $where_clause 
                 GROUP BY pt.id 
                 ORDER BY pt.id ASC");
$total_types = get_num_rows($result);
$types = array_slice(fetch($result), $offset, $per_page);

$total_pages = ceil($total_types / $per_page);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $img = '';
            if (isset($_FILES['type_img']) && $_FILES['type_img']['error'] === UPLOAD_ERR_OK) {
                if (is_image($_FILES['type_img'])) {
                    $img = generate_image_name($_FILES['type_img']);
                    if (!upload_image($_FILES['type_img'], $img, 'type')) {
                        show_alert('อัพโหลดรูปภาพไม่สำเร็จ');
                        break;
                    }
                } else {
                    show_alert('กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น');
                    break;
                }
            }

            $typeData = [
                'name' => $_POST['type_name'],
                'img' => $img
            ];

            if (insert('product_type', $typeData)) {
                show_alert('เพิ่มประเภทสินค้าสำเร็จ');
                reload_page();
            } else {
                show_alert('เพิ่มประเภทสินค้าไม่สำเร็จ');
            }
            break;

        case 'edit':
            $id = $_POST['id'];
            $typeData = [
                'name' => $_POST['type_name']
            ];

            if (isset($_FILES['type_img']) && $_FILES['type_img']['error'] === UPLOAD_ERR_OK) {
                if (is_image($_FILES['type_img'])) {
                    $img = generate_image_name($_FILES['type_img']);
                    if (upload_image($_FILES['type_img'], $img, 'type')) {
                        $typeData['img'] = $img;
                        
                        // Delete old image if exists
                        $old_img = fetch(get_by_id('product_type', $id), 2)['img'];
                        if ($old_img && file_exists(__DIR__ . '/../../upload/type/' . $old_img)) {
                            unlink(__DIR__ . '/../../upload/type/' . $old_img);
                        }
                    } else {
                        show_alert('อัพโหลดรูปภาพไม่สำเร็จ');
                        break;
                    }
                } else {
                    show_alert('กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น');
                    break;
                }
            }

            if (update_by_id('product_type', $id, $typeData)) {
                show_alert('แก้ไขประเภทสินค้าสำเร็จ');
                reload_page();
            } else {
                show_alert('แก้ไขประเภทสินค้าไม่สำเร็จ');
            }
            break;

        case 'delete':
            $id = $_POST['id'];
            
            // Check if there are products using this type
            $check_products = query("SELECT COUNT(*) as count FROM product WHERE type_id = $id");
            $product_count = fetch($check_products, 2)['count'];
            
            if ($product_count > 0) {
                show_alert('ไม่สามารถลบประเภทสินค้าได้ เนื่องจากมีสินค้าที่ใช้ประเภทนี้อยู่');
                break;
            }
            
            if (delete_by_id('product_type', $id)) {
                show_alert('ลบประเภทสินค้าสำเร็จ');
                reload_page();
            } else {
                show_alert('ลบประเภทสินค้าไม่สำเร็จ');
            }
            break;
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ประเภทสินค้า</h2>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addTypeModal">
            <i class="bi bi-plus-circle me-2"></i>เพิ่มประเภทสินค้า
        </button>
    </div>

    <!-- Search card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="product_types">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="search" 
                            placeholder="ค้นหาด้วยชื่อประเภทสินค้า..." 
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>รูปภาพ</th>
                            <th>ชื่อประเภทสินค้า</th>
                            <th class="text-center">จำนวนสินค้า</th>
                            <th class="text-center">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($types) > 0) : ?>
                            <?php foreach ($types as $i => $type) : ?>
                                <tr>
                                    <td class="align-middle"><?php echo $offset + $i + 1; ?></td>
                                    <td class="align-middle">
                                        <?php if ($type['img']) : ?>
                                            <img src="<?php echo '../upload/type/' . $type['img']; ?>" 
                                                alt="<?php echo $type['name']; ?>" 
                                                class="img-thumbnail" 
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else : ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?php echo $type['name']; ?></td>
                                    <td class="align-middle text-center"><?php echo $type['product_count']; ?></td>
                                    <td class="align-middle text-center dropdown">
                                        <button type="button" class="btn w-100 border-0" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editTypeModal" 
                                                    data-id="<?php echo $type['id']; ?>"
                                                    data-name="<?php echo $type['name']; ?>"
                                                    data-img="<?php echo $type['img']; ?>">
                                                    </i>แก้ไข
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" 
                                                    class="dropdown-item text-danger <?php echo ($type['product_count'] > 0) ? 'disabled' : ''; ?>" 
                                                    <?php if ($type['product_count'] == 0): ?>
                                                        onclick="deleteType(<?php echo $type['id']; ?>, <?php echo $type['product_count']; ?>)"
                                                    <?php endif; ?>
                                                    title="<?php echo ($type['product_count'] > 0) ? 'ไม่สามารถลบได้เนื่องจากมีสินค้าที่ใช้ประเภทนี้อยู่' : ''; ?>">
                                                    </i>ลบ
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">ไม่พบข้อมูลประเภทสินค้า</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    แสดง <?php echo count($types); ?> รายการ จากทั้งหมด <?php echo $total_types; ?> รายการ
                    <br>
                    <span class="text-danger small">*จะไม่สามารถลบประเภทสินค้าได้ ถ้ามีสินค้าที่ใช้ประเภทนั้นๆอยู่</span>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=product_types&p=<?php echo $current_page-1; ?>&search=<?php echo urlencode($search); ?>">ก่อนหน้า</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=product_types&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=product_types&p=<?php echo $current_page+1; ?>&search=<?php echo urlencode($search); ?>">ต่อไป</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Type Modal -->
<div class="modal" id="addTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มประเภทสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type_name" class="form-label">ชื่อประเภทสินค้า</label>
                        <input type="text" class="form-control" id="type_name" name="type_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="type_img" class="form-label">รูปภาพ</label>
                        <input type="file" class="form-control" id="type_img" name="type_img" accept="image/*">
                    </div>
                    <div id="preview_add" class="text-center mt-2" style="display: none;">
                        <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Type Modal -->
<div class="modal" id="editTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขประเภทสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_type_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_type_name" class="form-label">ชื่อประเภทสินค้า</label>
                        <input type="text" class="form-control" id="edit_type_name" name="type_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_type_img" class="form-label">รูปภาพ</label>
                        <input type="file" class="form-control" id="edit_type_img" name="type_img" accept="image/*">
                        <div class="form-text">ปล่อยว่างหากไม่ต้องการเปลี่ยนรูปภาพ</div>
                    </div>
                    <div id="current_img_edit" class="text-center mt-2 mb-3">
                        <p class="mb-2">รูปภาพปัจจุบัน</p>
                        <img src="" alt="Current" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                    <div id="preview_edit" class="text-center mt-2" style="display: none;">
                        <p class="mb-2">รูปภาพใหม่</p>
                        <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit modal
    document.querySelectorAll('[data-bs-target="#editTypeModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const img = this.dataset.img;
            const currentImgDiv = document.getElementById('current_img_edit');
            const currentImg = currentImgDiv.querySelector('img');
            
            document.getElementById('edit_type_id').value = id;
            document.getElementById('edit_type_name').value = name;
            
            // Handle current image display
            if (img) {
                currentImg.src = '../upload/type/' + img;
                currentImgDiv.style.display = 'block';
            } else {
                currentImgDiv.style.display = 'none';
            }
            
            // Reset file input and preview
            document.getElementById('edit_type_img').value = '';
            document.getElementById('preview_edit').style.display = 'none';
        });
    });

    // Handle delete
    window.deleteType = function(id, productCount) {
        if (productCount > 0) {
            alert('ไม่สามารถลบประเภทสินค้าได้ เนื่องจากมีสินค้าที่ใช้ประเภทนี้อยู่');
            return;
        }
        
        if (confirm('คุณต้องการลบประเภทสินค้านี้ใช่หรือไม่?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    // Image preview for add modal
    document.getElementById('type_img').addEventListener('change', function(e) {
        const preview = document.getElementById('preview_add');
        const previewImg = preview.querySelector('img');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
        }
    });

    // Update edit image preview handler
    document.getElementById('edit_type_img').addEventListener('change', function(e) {
        const preview = document.getElementById('preview_edit');
        const previewImg = preview.querySelector('img');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
        }
    });
});
</script> 