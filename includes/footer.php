<?php
/** Genel site alt bilgisi. */
$instagram = get_setting('social_instagram', '');
$etsy      = get_setting('social_etsy', '');
?>
</main>
<footer class="site-footer">
  <div class="container site-footer__inner">
    <span class="brand__text"><?= e(get_setting('logo_text', get_setting('site_title', ''))) ?></span>
    <div class="site-footer__social">
      <?php if ($instagram): ?><a href="<?= e($instagram) ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
      <?php if ($etsy): ?><a href="<?= e($etsy) ?>" target="_blank" rel="noopener">Etsy</a><?php endif; ?>
    </div>
    <p class="site-footer__copy"><?= e(get_setting('footer_text', '')) ?></p>
  </div>
</footer>
<script src="<?= e(asset_url('assets/js/main.js')) ?>"></script>
</body>
</html>
