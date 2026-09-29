<?php
// app/views/footer.php
$hm702_file = __DIR__.'/../header_menu_v702.php';
if(file_exists($hm702_file)) require_once $hm702_file;
$site_name = function_exists('hm702_brand_name') ? hm702_brand_name() : (function_exists('setting') ? setting('site_name', 'ShahkotPK') : 'ShahkotPK');
$site_tagline = function_exists('hm702_tagline') ? hm702_tagline() : (function_exists('setting') ? setting('site_tagline', 'Your ultimate city portal for discovering businesses, services, real estate, and emergency contacts in Shahkot.') : 'Your ultimate city portal for discovering businesses, services, real estate, and emergency contacts in Shahkot.');
?>
<footer class="global-footer">
    <div class="footer-container">
        <div class="footer-col brand-col">
            <h4><i class="nav-icon">🌍</i> <?= e($site_name) ?></h4>
            <p><?= e($site_tagline) ?></p>
            <div class="footer-socials">
                <a href="#" title="Facebook">📘</a>
                <a href="#" title="Twitter">🐦</a>
                <a href="#" title="Instagram">📸</a>
            </div>
        </div>
        <div class="footer-col">
            <h4><i class="nav-icon">🔗</i> Quick Links</h4>
            <ul>
                <li><a href="/businesses.php">Business Directory</a></li>
                <li><a href="/property.php">Real Estate</a></li>
                <li><a href="/jobs.php">Local Jobs</a></li>
                <li><a href="/events.php">City Events</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4><i class="nav-icon">🎧</i> Support</h4>
            <ul>
                <li><a href="/contact.php">Contact Us</a></li>
                <li><a href="/complaints.php">City Complaints</a></li>
                <li><a href="/privacy.php">Privacy Policy</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= e($site_name) ?>. All rights reserved.</p>
    </div>
</footer>
<script src="/assets/js/global.js"></script>
