<?php
/**
 * FOOTER.PHP - TEMPLATE FOOTER
 */
?>
        </div> <!-- end .container-fluid -->
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="fas fa-info-circle"></i> Về Tổ 4</h5>
                    <p>Website nội bộ dành cho các thành viên tổ 4 - lớp A4. Nơi chia sẻ tài liệu, công việc và những kỷ niệm đáng nhớ.</p>
                </div>
                
                <div class="col-md-4 mb-3">
                    <h5><i class="fas fa-link"></i> Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-light text-decoration-none">Trang chủ</a></li>
                        <li><a href="<?php echo BASE_URL; ?>members.php" class="text-light text-decoration-none">Thành viên</a></li>
                        <li><a href="<?php echo BASE_URL; ?>posts.php" class="text-light text-decoration-none">Tin tức</a></li>
                        <li><a href="<?php echo BASE_URL; ?>contact.php" class="text-light text-decoration-none">Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-3">
                    <h5><i class="fas fa-envelope"></i> Liên lạc</h5>
                    <p>Email: <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="text-light"><?php echo ADMIN_EMAIL; ?></a></p>
                    <p>Domain: <a href="https://g4a4.qzz.io" class="text-light">g4a4.qzz.io</a></p>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tất cả quyền được bảo lưu.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">Phát triển bởi <strong>Tổ 4</strong> với <i class="fas fa-heart text-danger"></i></p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    
    <!-- JavaScript tuỳ chỉnh -->
    <script>
        // Thông báo thành công
        function showSuccess(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
            setTimeout(() => alert.remove(), 3000);
        }
        
        // Thông báo lỗi
        function showError(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
            setTimeout(() => alert.remove(), 3000);
        }
    </script>
</body>
</html>
