<footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center">
                <p class="text-gray-400 text-sm">
                    © <?php echo date('Y'); ?> Football Jeunes Cameroun - Administration
                </p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
    // Notifications pour les actions admin
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier s'il y a des messages de session
        <?php if(isset($_SESSION['success'])): ?>
        showToast("<?php echo htmlspecialchars($_SESSION['success']); ?>", "success");
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
        showToast("<?php echo htmlspecialchars($_SESSION['error']); ?>", "error");
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>