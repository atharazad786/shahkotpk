<?php

declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/bloodbank_v570.php';
if (!bb570_enabled()) {
    http_response_code(404);
    exit('Blood Bank module unavailable.');
}
$banks = bb570_banks(true);
$inventory = (function_exists('setting_bool') && !setting_bool('bloodbank_v570_show_inventory', true)) ? array_fill_keys(bb570_groups(), 0) : bb570_inventory_summary();
$requests = (function_exists('setting_bool') && !setting_bool('bloodbank_v570_show_public_requests', true)) ? [] : bb570_public_requests(12);
$camps = bb570_camps(true, 8);
bb570_track('public_home_view');
bb570_public_header('Blood Bank & Donation Center', 'Find blood availability, donation appointments, urgent requests and community donation camps.');
?>
<main>
    <section class="bb570-slider" data-bb-slider data-autoplay="<?= setting_bool('bloodbank_v570_slider_autoplay', true) ? '1' : '0' ?>" data-seconds="<?= max(3, setting_int('bloodbank_v570_slider_seconds', 6)) ?>">
        <article class="bb570-slide active" style="--bb-bg:url('/assets/bloodbank/slide-save-lives.svg')">
            <div class="bb570-shell"><span class="eyebrow">COMMUNITY BLOOD BANK</span>
                <h1>One donation can support life-saving care.</h1>
                <p>Register as a donor, choose a blood bank and book a donation appointment in a few steps.</p>
                <div class="actions"><a class="btn primary" href="/blood-donate.php">♥ Donate Blood</a><a class="btn glass" href="#inventory">Check Availability</a></div>
            </div>
        </article>
        <article class="bb570-slide" style="--bb-bg:url('/assets/bloodbank/slide-find-blood.svg')">
            <div class="bb570-shell"><span class="eyebrow">URGENT REQUEST DESK</span>
                <h1>Request blood with privacy and verification controls.</h1>
                <p>Submit a request for review and let authorized blood-bank staff coordinate stock and fulfillment.</p>
                <div class="actions"><a class="btn primary" href="/blood-request.php">Request Blood</a><a class="btn glass" href="#requests">View Verified Requests</a></div>
            </div>
        </article>
        <article class="bb570-slide" style="--bb-bg:url('/assets/bloodbank/slide-camps.svg')">
            <div class="bb570-shell"><span class="eyebrow">DONATION CAMPS</span>
                <h1>Join local blood donation drives.</h1>
                <p>Discover upcoming camps, register your interest and stay connected with community blood-bank services.</p>
                <div class="actions"><a class="btn primary" href="#camps">Upcoming Camps</a><a class="btn glass" href="/my-blood.php">My Dashboard</a></div>
            </div>
        </article>
        <div class="bb570-slider-controls"><button data-bb-prev>‹</button>
            <div data-bb-dots></div><button data-bb-next>›</button>
        </div>
    </section>
    <section class="bb570-quick">
        <div class="bb570-shell" style="background: #ff4757; color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 20px; color: white;">🚨 EMERGENCY</h2>
                <span style="font-size: 14px; opacity: 0.9;">One-tap dial for critical emergencies in Shahkot.</span>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="tel:1122" class="btn" style="background: white; color: #ff4757; font-weight: bold; padding: 10px 20px; border-radius: 25px;">🚑 Ambulance 1122</a>
                <a href="tel:15" class="btn" style="background: white; color: #1e3799; font-weight: bold; padding: 10px 20px; border-radius: 25px;">🚓 Police 15</a>
            </div>
        </div>
        <div class="bb570-shell bb570-quick-grid">
            <a href="/blood-donate.php"><i>♥</i><b>Become a Donor</b><span>Register & book</span></a>
            <a href="/blood-request.php"><i>＋</i><b>Request Blood</b><span>Verified request flow</span></a>
            <a href="#inventory"><i>◫</i><b>Blood Availability</b><span>Live stock summary</span></a>
            <a href="#camps"><i>◆</i><b>Donation Camps</b><span>Upcoming drives</span></a>
        </div>
    </section>
    <section class="bb570-section" id="inventory">
        <div class="bb570-shell">
            <div class="section-head">
                <div><span>LIVE INVENTORY</span>
                    <h2>Blood availability at a glance</h2>
                    <p>Public counts include only cleared, available and non-expired stock batches.</p>
                </div><a href="/blood-request.php">Need blood? →</a>
            </div>
            <div class="bb570-groups"><?php foreach ($inventory as $g => $n): ?><div class="group-card <?= $n < 2 ? 'low' : '' ?>">
                        <div class="drop">♥</div><b><?= e($g) ?></b><strong><?= number_format($n) ?></strong><span>unit<?= $n === 1 ? '' : 's' ?> available</span>
                    </div><?php endforeach; ?></div>
            <div class="bb570-medical-note">Availability shown online is informational. Final issue, testing, crossmatch and transfusion decisions remain with qualified medical/blood-bank staff.</div>
        </div>
    </section>
    <section class="bb570-section alt">
        <div class="bb570-shell">
            <div class="section-head">
                <div><span>OUR NETWORK</span>
                    <h2>Blood banks & donation centers</h2>
                    <p>Verified contact, operating hours, location and emergency-service information.</p>
                </div>
            </div><?php if (!$banks): ?><div class="bb570-empty"><b>Blood bank directory is being configured.</b><span>Please check again soon or contact the city support team.</span></div><?php else: ?><div class="bb570-bank-grid"><?php foreach ($banks as $b): ?><article class="bank-card">
                            <div class="bank-icon">♥</div>
                            <div>
                                <div class="badges"><?php if ($b['verified']): ?><span>VERIFIED</span><?php endif; ?><?php if ($b['emergency_available']): ?><span class="urgent">24/7 EMERGENCY</span><?php endif; ?></div>
                                <h3><?= e($b['name']) ?></h3>
                                <p>⌖ <?= e($b['address'] ?: $b['area']) ?></p>
                                <div class="bank-meta"><span>☎ <?= e($b['phone'] ?: 'Contact via portal') ?></span><span>✉ <?= e($b['email'] ?: '—') ?></span></div>
                                <div class="bank-actions"><a href="/blood-donate.php?bank=<?= (int)$b['id'] ?>">Book Donation</a><?php if ($b['map_url']): ?><a class="secondary" target="_blank" rel="noopener" href="<?= e($b['map_url']) ?>">Map ↗</a><?php endif; ?></div>
                            </div>
                        </article><?php endforeach; ?></div><?php endif; ?>
        </div>
    </section>
    <section class="bb570-section" id="requests">
        <div class="bb570-shell">
            <div class="section-head">
                <div><span>VERIFIED REQUESTS</span>
                    <h2>Active blood requests</h2>
                    <p>Patient identity and personal contact details are not displayed publicly.</p>
                </div><a href="/blood-request.php">Submit Request →</a>
            </div><?php if (!$requests): ?><div class="bb570-empty"><b>No verified public requests right now.</b><span>New requests will appear here after staff verification.</span></div><?php else: ?><div class="bb570-request-grid"><?php foreach ($requests as $r): ?><article class="request-card <?= $r['urgency'] === 'emergency' ? 'emergency' : '' ?>">
                            <div class="request-top"><span class="urgency"><?= e(strtoupper($r['urgency'])) ?></span><span><?= e($r['request_no']) ?></span></div>
                            <div class="request-group"><?= e($r['blood_group']) ?></div>
                            <h3><?= e($r['hospital_name']) ?></h3>
                            <p><?= e($r['hospital_area'] ?: 'Local area') ?> · <?= e(bb570_components()[$r['component']] ?? $r['component']) ?></p>
                            <div class="request-facts"><b><?= max(0, (int)$r['units_needed'] - (int)$r['units_fulfilled']) ?> units needed</b><span><?= e($r['needed_at'] ? date('d M · h:i A', strtotime($r['needed_at'])) : 'As soon as possible') ?></span></div><a href="/blood-donate.php?group=<?= urlencode($r['blood_group']) ?>">I can donate →</a>
                        </article><?php endforeach; ?></div><?php endif; ?>
        </div>
    </section>
    <section class="bb570-section alt" id="camps">
        <div class="bb570-shell">
            <div class="section-head">
                <div><span>COMMUNITY DRIVES</span>
                    <h2>Upcoming donation camps</h2>
                    <p>Register your interest and complete final screening with authorized staff at the venue.</p>
                </div>
            </div><?php if (!$camps): ?><div class="bb570-empty"><b>No upcoming camps published.</b><span>Donation drives added by blood-bank staff will appear here.</span></div><?php else: ?><div class="bb570-camp-grid"><?php foreach ($camps as $c): ?><article class="camp-card">
                            <div class="camp-date"><b><?= date('d', strtotime($c['starts_at'])) ?></b><span><?= strtoupper(date('M', strtotime($c['starts_at']))) ?></span></div>
                            <div><span class="camp-bank"><?= e($c['bank_name'] ?: $c['organizer']) ?></span>
                                <h3><?= e($c['title']) ?></h3>
                                <p>⌖ <?= e($c['venue']) ?></p>
                                <p>◷ <?= e(date('D, d M Y · h:i A', strtotime($c['starts_at']))) ?></p><a href="/blood-donate.php?camp=<?= (int)$c['id'] ?>">Register / Donate →</a>
                            </div>
                        </article><?php endforeach; ?></div><?php endif; ?>
        </div>
    </section>

</main>
<?php bb570_public_footer(); ?>