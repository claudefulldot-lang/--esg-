        </div> <!-- /container-fluid -->
    </div> <!-- /#page-content-wrapper -->
</div> <!-- /#wrapper -->

<?php if (!empty($currentUser)): ?>
    <?php require __DIR__ . '/ai_assistant.php'; ?>
<?php endif; ?>

<!-- Footer info -->
<footer class="footer py-3 bg-white border-top text-center text-muted small">
    <div class="container">
        <span>&copy; <?= date('Y') ?> ESG-Pro 企業級智慧管理與碳盤查系統 | 符合 ISO 14064-1:2018 / GHG Protocol / GRI Standards 2021</span>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js 4 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php foreach (($extraScripts ?? []) as $script): ?>
<script src="<?= \App\Helpers\Security::e($script) ?>" defer></script>
<?php endforeach; ?>
<script src="/esg/assets/js/ai-assistant.js" defer></script>

<!-- Global Application Script -->
<script>
$(document).ready(function() {
    // Sidebar toggle (Desktop collapse / Mobile drawer open)
    $('#sidebarToggle').on('click', function(e) {
        e.preventDefault();
        $('#wrapper').toggleClass('toggled');
    });

    // Mobile Sidebar Close button & Backdrop click
    $('#sidebarClose, #sidebar-backdrop').on('click', function(e) {
        e.preventDefault();
        $('#wrapper').removeClass('toggled');
    });

    // Auto-close mobile drawer when navigation links are clicked on small screens
    $('#sidebar-wrapper .list-group-item').on('click', function() {
        if ($(window).width() < 992) {
            $('#wrapper').removeClass('toggled');
        }
    });

    // Clean up toggled class when resizing above 992px
    $(window).on('resize', function() {
        if ($(window).width() >= 992) {
            // Keep desktop state clean
        }
    });

    // Initialize all standard datatables
    $('.data-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/zh-HANT.json'
        },
        pageLength: 15,
        responsive: true,
        autoWidth: false
    });
});
</script>
</body>
</html>
