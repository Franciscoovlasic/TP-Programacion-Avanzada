    <footer class="site-footer">
        <span>&copy; <?php echo date("Y"); ?> FORJA GYM</span>
        <span class="footer-divider">·</span>
        <span>Trabajo Práctico N.º 1 — Programación Avanzada</span>
    </footer>

<?php if (!empty($usaDataTables)): ?>
    <script src="https://cdn.datatables.net/3.0.2/js/dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($rutaBase . 'js/tablas.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endif; ?>
</body>
</html>
