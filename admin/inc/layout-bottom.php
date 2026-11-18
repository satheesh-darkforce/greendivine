    </main>
  </div>
</div>
<script src="<?= e(gd_asset('/assets/vendor/bootstrap-5.3.3/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(gd_asset('/admin/assets/js/admin-core.js')) ?>"></script>
<?php if (!empty($adminExtraJs)): foreach ((array)$adminExtraJs as $js): ?>
<script src="<?= e(gd_asset('/admin/assets/js/' . $js)) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
