<?php
include_once "../core/helpers/image_upload.php";

// Pagination setup
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 9; // Number of banners per page
$offset = ($current_page - 1) * $per_page;

// Get search and status filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build where conditions for filtering
$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "img LIKE '%$search%'";
}
if ($status_filter !== '') {
    $where_conditions[] = "status = '$status_filter'";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total banners and paginated results
$result = query("SELECT * FROM banner $where_clause ORDER BY sort ASC, id DESC");
$total_banners = get_num_rows($result);
$banners = array_slice(fetch($result), $offset, $per_page);

$total_pages = ceil($total_banners / $per_page);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            if (isset($_FILES['banner_image'])) {
                $file = $_FILES['banner_image'];
                
                if (!is_image($file)) {
                    show_alert('กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น');
                    break;
                }

                $filename = generate_image_name($file);
                
                if (upload_image($file, $filename, 'banner/')) {
                    $bannerData = [
                        'img' => $filename,
                        'status' => $_POST['status'],
                        'update_date' => date('Y-m-d H:i:s')
                    ];

                    if (insert('banner', $bannerData)) {
                        show_alert('เพิ่มป้ายโฆษณาสำเร็จ');
                        reload_page();
                    } else {
                        show_alert('เพิ่มป้ายโฆษณาไม่สำเร็จ');
                    }
                } else {
                    show_alert('อัพโหลดรูปภาพไม่สำเร็จ');
                }
            }
            break;

        case 'edit':
            $id = $_POST['id'];
            $bannerData = [
                'status' => $_POST['status'],
                'update_date' => date('Y-m-d H:i:s')
            ];

            // Handle file upload if new image is provided
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['size'] > 0) {
                $file = $_FILES['banner_image'];
                
                if (!is_image($file)) {
                    show_alert('กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น');
                    break;
                }

                $filename = generate_image_name($file);
                
                if (upload_image($file, $filename, 'banner/')) {
                    $bannerData['img'] = $filename;
                } else {
                    show_alert('อัพโหลดรูปภาพไม่สำเร็จ');
                    break;
                }
            }

            if (update_by_id('banner', $id, $bannerData)) {
                show_alert('แก้ไขป้ายโฆษณาสำเร็จ');
                reload_page();
            } else {
                show_alert('แก้ไขป้ายโฆษณาไม่สำเร็จ');
            }
            break;

        case 'delete':
            $id = $_POST['id'];
            if (delete_by_id('banner', $id)) {
                show_alert('ลบป้ายโฆษณาสำเร็จ');
                reload_page();
            } else {
                show_alert('ลบป้ายโฆษณาไม่สำเร็จ');
            }
            break;

        case 'sort':
            $id = $_POST['id'];
            $sort = (int)$_POST['sort'];
            
            $bannerData = [
                'sort' => $sort,
                'update_date' => date('Y-m-d H:i:s')
            ];

            if (update_by_id('banner', $id, $bannerData)) {
                show_alert('จัดลำดับป้ายโฆษณาสำเร็จ');
                reload_page();
            } else {
                show_alert('จัดลำดับป้ายโฆษณาไม่สำเร็จ');
            }
            exit;
            break;
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ป้ายโฆษณา</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal">
            <i class="bi bi-plus-circle me-2"></i>เพิ่มป้ายโฆษณาใหม่
        </button>
    </div>

    <!-- Search card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="banners">
                <div class="row g-3">
                    <div class="col-md-10">
                        <select class="form-select" name="status">
                            <option value="">ทุกสถานะ</option>
                            <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>เปิดใช้งาน</option>
                            <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>ปิดใช้งาน</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">กรอง</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add this section after the filter card and before the banner grid -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">ตัวอย่างการแสดงผลบนหน้าเว็บ</h5>
            <?php if (count($banners) > 0) : ?>
                <div id="previewCarousel" class="carousel slide">
                    <div class="carousel-inner">
                        <?php foreach ($banners as $index => $banner) : ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="../upload/banner/<?php echo $banner['img']; ?>" 
                                     class="d-block w-100" alt="Banner" 
                                     style="object-fit: cover; height: 400px;"
                                     onerror="this.onerror=null; this.src='../assets/images/404_banner.png';">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#previewCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#previewCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            <?php else : ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-image display-1"></i>
                    <p class="mt-3">ไม่มีป้ายโฆษณาที่เปิดใช้งาน</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Banners grid -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <?php if (count($banners) > 0) : ?>
                    <?php foreach ($banners as $banner) : ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <span class="input-group-text">ลำดับ</span>
                                        <input type="number" class="form-control sort-input" 
                                               value="<?php echo $banner['sort']; ?>" 
                                               data-id="<?php echo $banner['id']; ?>"
                                               min="0">
                                    </div>
                                    <span class="badge bg-<?php echo $banner['status'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $banner['status'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน'; ?>
                                    </span>
                                </div>
                                <img src="../upload/banner/<?php echo $banner['img']; ?>" 
                                     class="card-img-top" alt="Banner"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            อัพเดทล่าสุด: <?php echo date('d/m/Y H:i', strtotime($banner['update_date'])); ?>
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editBannerModal"
                                                data-id="<?php echo $banner['id']; ?>">
                                            แก้ไข
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteBanner(<?php echo $banner['id']; ?>)">
                                            ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12 text-center py-4">
                        <p class="mb-0">ไม่พบข้อมูลป้ายโฆษณา</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    แสดง <?php echo count($banners); ?> รายการ จากทั้งหมด <?php echo $total_banners; ?> รายการ
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=banners&p=<?php echo $current_page-1; ?>&status=<?php echo urlencode($status_filter); ?>">ก่อนหน้า</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=banners&p=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=banners&p=<?php echo $current_page+1; ?>&status=<?php echo urlencode($status_filter); ?>">ต่อไป</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Banner Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มป้ายโฆษณาใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addBannerForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="banner_image" class="form-label">รูปภาพ</label>
                        <input type="file" class="form-control" id="banner_image" name="banner_image" 
                               accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">สถานะ</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1">เปิดใช้งาน</option>
                            <option value="0">ปิดใช้งาน</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="addBannerForm" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Banner Modal -->
<div class="modal fade" id="editBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขป้ายโฆษณา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBannerForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">รูปภาพปัจจุบัน</label>
                        <img id="current_image" src="" alt="Current Banner" 
                             class="img-fluid rounded mb-2" style="max-height: 200px;">
                    </div>
                    <div class="mb-3">
                        <label for="edit_banner_image" class="form-label">อัพโหลดรูปภาพใหม่</label>
                        <input type="file" class="form-control" id="edit_banner_image" 
                               name="banner_image" accept="image/*">
                        <small class="text-muted">เว้นว่างหากไม่ต้องการเปลี่ยนรูปภาพ</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">สถานะ</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="1">เปิดใช้งาน</option>
                            <option value="0">ปิดใช้งาน</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="editBannerForm" class="btn btn-primary">บันทึก</button>
            </div>
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
    document.querySelectorAll('[data-bs-target="#editBannerModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const bannerId = this.dataset.id;
            fetch(`../core/helpers/get_data.php?type=banner&id=${bannerId}`)
                .then(response => response.json())
                .then(banner => {
                    document.getElementById('edit_id').value = banner.id;
                    document.getElementById('edit_status').value = banner.status;
                    document.getElementById('current_image').src = '../upload/banner/' + banner.img;
                });
        });
    });

    // Handle delete
    window.deleteBanner = function(id) {
        if (confirm('คุณต้องการลบป้ายโฆษณานี้ใช่หรือไม่?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    // Add sort handling
    let sortTimeout;
    document.querySelectorAll('.sort-input').forEach(input => {
        input.addEventListener('change', function() {
            const bannerId = this.dataset.id;
            const sortValue = this.value;

            clearTimeout(sortTimeout);
            sortTimeout = setTimeout(() => {
                updateSort(bannerId, sortValue);
            }, 500);
        });
    });

    function updateSort(id, sort) {
        // Create a hidden form
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        // Add action input
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'sort';
        form.appendChild(actionInput);

        // Add id input
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);

        // Add sort input
        const sortInput = document.createElement('input');
        sortInput.type = 'hidden';
        sortInput.name = 'sort';
        sortInput.value = sort;
        form.appendChild(sortInput);

        // Add form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
});
</script> 