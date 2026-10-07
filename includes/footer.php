<?php
/**
 * SponsorFlow - Application Footer Shell
 */
?>
        </main>
    </div>
</div>

<div id="toast" role="status" aria-live="polite"></div>

<script src="assets/js/app.js"></script>
<?php if (!empty($extraJs)): ?>
    <script src="<?= e($extraJs) ?>"></script>
<?php endif; ?>
</body>
</html>
