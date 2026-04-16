<?php
include_once "../core/helpers/image_upload.php";

// Add these lines near the top of the file after including helpers
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10; // Number of products per page
$offset = ($current_page - 1) * $per_page;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $productData = [
                'name' => $_POST['product_name'],
                'detail' => $_POST['product_description'],
                'price' => $_POST['product_price'],
                'stock' => $_POST['product_quantity'],
                'type_id' => $_POST['product_type']
            ];

            // Handle image upload only if an image was provided
            if (!empty($_FILES['product_image']['name'])) {
                $image = $_FILES['product_image'];
                if (!is_image($image)) {
                    show_alert('กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น');
                    break;
                }

                $image_name = generate_image_name($image);
                if (!upload_image($image, $image_name, "product/")) {
                    show_alert('อัพโหลดรูปภาพไม่สำเร็จ');
                    break;
                }

                $productData['img'] = $image_name;
            } else {
                // Set a default image name or null depending on your database structure
                $productData['img'] = 'default.jpg'; // or whatever your default image is named
            }

            if (insert('product', $productData)) {
                show_alert('เพิ่มสินค้าสำเร็จ');
                reload_page();
            } else {
                show_alert('เพิ่มสินค้าไม่สำเร็จ');
            }
            break;

        case 'edit':
            $id = $_POST['id'];
            $productData = [
                'name' => $_POST['product_name'],
                'detail' => $_POST['product_description'],
                'price' => $_POST['product_price'],
                'stock' => $_POST['product_quantity'],
                'type_id' => $_POST['product_type']
            ];

            // Handle image upload if new image is provided
            if (!empty($_FILES['product_image']['name'])) {
                $image = $_FILES['product_image'];
                if (!is_image($image)) {
                    show_alert('กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น');
                    break;
                }

                $image_name = generate_image_name($image);
                if (!upload_image($image, $image_name, "product/")) {
                    show_alert('อัพโหลดรูปภาพไม่สำเร็จ');
                    break;
                }

                $productData['img'] = $image_name;
            }

            if (update_by_id('product', $id, $productData)) {
                show_alert('แก้ไขสินค้าสำเร็จ');
                reload_page();
            } else {
                show_alert('แก้ไขสินค้าไม่สำเร็จ');
            }
            break;

        case 'delete':
            $id = $_POST['id'];
            if (delete_by_id('product', $id)) {
                show_alert('ลบสินค้าสำเร็จ');
                reload_page();
            } else {
                show_alert('ลบสินค้าไม่สำเร็จ');
            }
            break;
    }
}

$product_types = fetch(get_all('product_type')); // Get all product types once
?>

<div class="container-fluid mb-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>รายการสินค้า</h2>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle me-2"></i>เพิ่มสินค้าใหม่
        </button>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="">
            <input type="hidden" name="page" value="products">
                <div class="row g-3">
                    <div class="col-md-7">
                        <input type="text" name="search" class="form-control" placeholder="ค้นหาสินค้า..." 
                               value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">ทุกประเภท</option>
                            <?php
                            foreach ($product_types as $type) {
                                $selected = (isset($_GET['type']) && $_GET['type'] == $type['id']) ? 'selected' : '';
                                echo "<option value='{$type['id']}' {$selected}>{$type['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-0 overflow-y-auto">
            <div class="table-responsive rounded-3" style="min-height: 15rem;">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>รูปภาพ</th>
                            <th>ชื่อสินค้า</th>
                            <th>รายละเอียด</th>
                            <th>ประเภท</th>
                            <th class="text-end">ราคา (บาท)</th>
                            <th class="text-end">คงเหลือ</th>
                            <th class="text-center">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="overflow-y-auto">
                    <?php
                    // Get search parameters
                    $search = isset($_GET['search']) ? $_GET['search'] : '';
                    $type = isset($_GET['type']) ? $_GET['type'] : '';

                    // Build the query conditions
                    $conditions = [];
                    if (!empty($search)) {
                        $conditions[] = "p.name LIKE '%$search%'";
                    }
                    if (!empty($type)) {
                        $conditions[] = "p.type_id = '$type'";
                    }

                    // Combine conditions
                    $where = '';
                    if (!empty($conditions)) {
                        $where = "WHERE " . implode(' AND ', $conditions);
                    }

                    // Modify the query section to include pagination
                    $sql = "SELECT p.*, pt.name as type_name , pt.id as type_id
                            FROM product p 
                            LEFT JOIN product_type pt ON p.type_id = pt.id 
                            $where 
                            ORDER BY p.id DESC";
                    $result = query($sql);
                    $total_products = get_num_rows($result);
                    $products = array_slice(fetch($result), $offset, $per_page);

                    $total_pages = ceil($total_products / $per_page);
                    
                    if (get_num_rows($result) > 0) {
                        foreach ($products as $index => $product) {
                            ?>
                            <tr>
                                <td class="align-middle text-center"><?php echo $index + 1; ?></td>
                                <td class="align-middle">
                                    <img src="../upload/product/<?php echo $product['img']; ?>" 
                                         width="50" 
                                         onerror="this.onerror=null; this.src='../assets/images/404.webp';">
                                </td>
                                <td class="align-middle">
                                    <div class="text-truncate" style="max-width: 15rem;" 
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         title="<?php echo htmlspecialchars($product['name']); ?>">
                                        <a href="./../?page=product&id=<?php echo $product['id']; ?>" class="text-decoration-none" target="_blank">
                                            <?php echo $product['name'] ?>
                                        </a>
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.6rem;"></i>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="text-truncate" style="max-width: 30rem;" 
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         title="<?php echo htmlspecialchars($product['detail']); ?>">
                                        <?php echo $product['detail']; ?>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <a href="?page=products&type=<?php echo $product['type_id']; ?>" class="text-decoration-none" target="_self">
                                        <?php echo $product['type_name']; ?>
                                    </a>
                                </td>
                                <td class="align-middle text-end"><?php echo number_format($product['price'], 2); ?></td>
                                <td class="align-middle text-end"><?php echo number_format($product['stock']); ?></td>
                                <td class="align-middle text-center dropdown">
                                    <button type="button" class="btn w-100 border-0" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button type="button" class="dropdown-item" 
                                                onclick="editProduct(<?php echo $product['id']; ?>)">
                                                แก้ไข
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" 
                                                onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                ลบ
                                            </button>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">ไม่พบข้อมูลสินค้า</td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <div>
                    แสดง <?php echo count($products); ?> รายการ จากทั้งหมด <?php echo $total_products; ?> รายการ
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=products&p=<?php echo $current_page-1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>">ก่อนหน้า</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=products&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=products&p=<?php echo $current_page+1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>">ต่อไป</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">เพิ่มสินค้าใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="product_name" class="form-label">ชื่อสินค้า</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" 
                               maxlength="255" placeholder="กรุณากรอกชื่อสินค้า" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="product_type" class="form-label">ประเภทสินค้า</label>
                            <select class="form-select" id="product_type" name="product_type" required>
                                <option value="" selected disabled>กรุณาเลือกประเภทสินค้า</option>
                                <?php
                                foreach ($product_types as $type) {
                                    echo "<option value='{$type['id']}'>{$type['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="product_description" class="form-label">รายละเอียดสินค้า</label>
                        <textarea class="form-control" id="product_description" name="product_description" 
                                  rows="4" placeholder="กรุณากรอกรายละเอียดสินค้า"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_price" class="form-label">ราคา</label>
                            <input type="number" class="form-control" id="product_price" name="product_price" 
                                   min="0" step="1" placeholder="กรุณากรอกราคาสินค้า" required>
                        </div>
                        <div class="col-md-6">
                            <label for="product_quantity" class="form-label">จำนวน</label>
                            <input type="number" class="form-control" id="product_quantity" name="product_quantity" 
                                   min="0" placeholder="กรุณากรอกจำนวนสินค้า" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="product_image" class="form-label">รูปภาพสินค้า (ไม่บังคับ)</label>
                        <input type="file" class="form-control" id="product_image" name="product_image" 
                               accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="addProductForm" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">แก้ไขสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label for="edit_product_name" class="form-label">ชื่อสินค้า</label>
                        <input type="text" class="form-control" id="edit_product_name" name="product_name" 
                               maxlength="255" placeholder="กรุณากรอกชื่อสินค้า" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="edit_product_type" class="form-label">ประเภทสินค้า</label>
                            <select class="form-select" id="edit_product_type" name="product_type" required>
                                <option value="" disabled>กรุณาเลือกประเภทสินค้า</option>
                                <?php
                                foreach ($product_types as $type) {
                                    echo "<option value='{$type['id']}'>{$type['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_product_description" class="form-label">รายละเอียดสินค้า</label>
                        <textarea class="form-control" id="edit_product_description" name="product_description" 
                                  rows="4" placeholder="กรุณากรอกรายละเอียดสินค้า"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_product_price" class="form-label">ราคา</label>
                            <input type="number" class="form-control" id="edit_product_price" name="product_price" 
                                   min="0" step="0.01" placeholder="กรุณากรอกราคาสินค้า" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_product_quantity" class="form-label">จำนวน</label>
                            <input type="number" class="form-control" id="edit_product_quantity" name="product_quantity" 
                                   min="0" placeholder="กรุณากรอกจำนวนสินค้า" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_product_image" class="form-label">รูปภาพสินค้า</label>
                        <input type="file" class="form-control" id="edit_product_image" name="product_image" 
                               accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="editProductForm" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
            new bootstrap.Tooltip(tooltipTriggerEl)
        );

        // Handle edit product
        window.editProduct = function(id) {
            fetch(`../core/helpers/get_data.php?type=product&id=${id}`)
                .then(response => response.json())
                .then(product => {
                    document.getElementById('edit_id').value = product.id;
                    document.getElementById('edit_product_name').value = product.name;
                    document.getElementById('edit_product_description').value = product.detail;
                    document.getElementById('edit_product_price').value = product.price;
                    document.getElementById('edit_product_quantity').value = product.stock;
                    document.getElementById('edit_product_type').value = product.type_id;
                    
                    // Show the edit modal
                    new bootstrap.Modal(document.getElementById('editProductModal')).show();
                });
        }

        // Handle delete product
        window.deleteProduct = function(id) {
            if (confirm('คุณต้องการลบสินค้านี้ใช่หรือไม่?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Add form validation
        const validateForm = (form) => {
            const name = form.querySelector('[name="product_name"]').value;
            const price = parseFloat(form.querySelector('[name="product_price"]').value);
            const stock = parseInt(form.querySelector('[name="product_quantity"]').value);

            if (name.length > 255) {
                alert('ชื่อสินค้าต้องไม่เกิน 255 ตัวอักษร');
                return false;
            }

            if (price < 0 || price > 9999999.99) {
                alert('ราคาสินค้าไม่ถูกต้อง');
                return false;
            }

            if (stock < 0 || stock > 999999999) {
                alert('จำนวนสินค้าไม่ถูกต้อง');
                return false;
            }

            return true;
        };

        // Add validation to forms
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });

        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
</script>

<!-- Add hidden delete form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form> 