<?php
/**
 * Copyright (c) 2026 iCerya (icerya.com). All Rights Reserved.
 *
 * This file contains Original Code and/or Modifications of Original Code
 * as defined in and that are subject to the Apple Public Source License
 * Version 2.0 (the 'License'). You may not use this file except in
 * compliance with the License. Please obtain a copy of the License at
 * http://www.opensource.apple.com/apsl/ and read it before using this file.
 *
 * The Original Code and all software distributed under the License are
 * distributed on an 'AS IS' basis, WITHOUT WARRANTY OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, AND ICERYA HEREBY DISCLAIMS ALL SUCH WARRANTIES,
 * INCLUDING WITHOUT LIMITATION, ANY WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE, QUIET ENJOYMENT OR NON-INFRINGEMENT.
 * Please see the License for the specific language governing rights and
 * limitations under the License.
 *
 * @package   TrailblazerUI
 * @author    iCerya
 * @link      https://icerya.com
 *
 * 共享渲染函数 — 同时被 connector.php 和 wp_connector.php 加载
 */

// ---------------------------------------------------------------------------
// WordPress 兼容层：独立 PHP 环境下提供 esc_html / esc_url polyfill
// ---------------------------------------------------------------------------
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $_context = 'display') {
        $url = (string) $url;
        // 基础 XSS 防护：屏蔽危险协议
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return '';
        }
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

// ---------------------------------------------------------------------------
// 年度进度计算
// ---------------------------------------------------------------------------
if (!function_exists('trailblazer_calc_progress')) {
    function trailblazer_calc_progress() {
        $isLeapYear = date('L');
        $totalDays  = $isLeapYear ? 366 : 365;
        $daysPassed = date('z') + 1;
        $daysLeft   = $totalDays - $daysPassed;
        $percentage = round(($daysPassed / $totalDays) * 100, 1);

        return compact('daysPassed', 'daysLeft', 'totalDays', 'percentage');
    }
}

// ---------------------------------------------------------------------------
// 智能图片路径：自动补全绝对路径 + 深色模式后缀探测
// ---------------------------------------------------------------------------
if (!function_exists('trailblazer_smart_img')) {
    function trailblazer_smart_img($url, $theme, $baseUrl) {
        if (empty($url)) {
            return '';
        }

        $cleanUrl = ltrim($url, './');
        $finalUrl = preg_match('#^https?://#', $cleanUrl)
            ? $cleanUrl
            : rtrim($baseUrl, '/') . '/' . $cleanUrl;

        return $finalUrl;
    }
}

// ---------------------------------------------------------------------------
// HTML 渲染（纯输出，不依赖 WordPress）
// ---------------------------------------------------------------------------
if (!function_exists('trailblazer_render_html')) {
    function trailblazer_render_html($data, $themeMode, $baseUrl) {
        $progress = trailblazer_calc_progress();
        $daysPassed = $progress['daysPassed'];
        $daysLeft   = $progress['daysLeft'];
        $percentage = $progress['percentage'];

        $coverImg = trailblazer_smart_img(
            $data['nextDestinations']['cover'] ?? '',
            $themeMode,
            $baseUrl
        );

        ob_start();
        ?>
        <div class="tb-page-wrapper">
            <div class="tb-hero">
                <h1 class="tb-title">愿此行，终抵群星。</h1>
                <p class="tb-progress-label">今年已过了...</p>
                <div class="tb-progress-row">
                    <div class="tb-progress-bar-bg">
                        <div class="tb-progress-bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                    <div class="tb-progress-percent"><?php echo $percentage; ?>%</div>
                </div>
                <p class="tb-progress-desc">距离开拓历新年还有 <?php echo $daysLeft; ?> 天。</p>
            </div>

            <?php if (!empty($data)): ?>
            <div class="tb-dashboard-card">
                <div class="tb-dash-left">
                    <div class="tb-pill"><iconify-icon icon="mingcute:flight-takeoff-line"></iconify-icon> 拓星旅迹 <span class="sub-text">Where is next?</span></div>

                    <div class="tb-stats-group">
                        <div class="tb-stat-item">
                            <span class="tb-num"><?php echo esc_html($data['stats']['cities'] ?? 0); ?></span>
                            <span class="tb-unit">个星球</span>
                            <span class="tb-desc">已开拓</span>
                        </div>
                        <div class="tb-stat-item">
                            <span class="tb-num"><?php echo esc_html($data['stats']['countries'] ?? 0); ?></span>
                            <span class="tb-unit">个文明</span>
                            <div class="tb-distance-pill"><iconify-icon icon="mingcute:git-commit-line"></iconify-icon> 银轨铺设 <?php echo esc_html($data['stats']['distance'] ?? 0); ?>km</div>
                        </div>
                    </div>

                    <p class="tb-text-desc">列车启程，向着下一目标跃迁中...</p>
                    <div class="tb-divider">
                        <div class="tb-dashed-line"></div>
                    </div>

                    <div class="tb-unlocked">
                        <span class="tb-unlocked-title"><iconify-icon icon="mingcute:send-plane-fill"></iconify-icon> 已解锁星球</span>
                        <div class="tb-city-pills">
                            <?php foreach (($data['unlockedCities'] ?? []) as $city): ?>
                                <span class="city-pill"><iconify-icon icon="mingcute:location-2-line"></iconify-icon> <?php echo htmlspecialchars($city); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="tb-dash-right">
                    <div class="tb-next-cover">
                        <img src="<?php echo esc_url($coverImg); ?>" alt="Next Stop">
                    </div>
                    <div class="tb-next-header">
                        <div class="tb-next-title">下一站 <span class="next-badge">Next →</span></div>
                        <div class="tb-next-tag"><iconify-icon icon="mingcute:building-3-fill"></iconify-icon> <?php echo htmlspecialchars($data['nextDestinations']['tag'] ?? '未知'); ?></div>
                    </div>
                    <div class="tb-plans-grid">
                        <?php foreach (($data['nextDestinations']['plans'] ?? []) as $plan): ?>
                            <div class="tb-plan-card <?php echo $plan['isActive'] ? 'active' : ''; ?>">
                                <h4><?php echo htmlspecialchars($plan['country']); ?></h4>
                                <p><?php echo htmlspecialchars($plan['cities']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['tickets'])): ?>
            <div class="tb-tickets-section">
                <h2 class="tb-section-title"><?php echo htmlspecialchars($data['nextDestinations']['tag'] ?? '旅程记录'); ?></h2>
                <div class="tb-tickets-grid">
                    <?php foreach (($data['tickets'] ?? []) as $ticket): ?>
                        <?php
                        $ticketTag = !empty($ticket['link']) ? 'a' : 'div';
                        $ticketAttr = !empty($ticket['link']) ? 'href="' . esc_url($ticket['link']) . '" target="_blank" rel="noopener"' : '';
                        $bannerColor = !empty($ticket['color']) ? 'rgba(' . $ticket['color'] . ')' : 'rgba(128, 128, 128, 0.07)';
                        $ticketIcon = trailblazer_smart_img($ticket['icon'] ?? '', $themeMode, $baseUrl);
                        $ticketStamp = trailblazer_smart_img($ticket['Stamp'] ?? '', $themeMode, $baseUrl);
                        $ticketImg = trailblazer_smart_img($ticket['img'] ?? '', $themeMode, $baseUrl);
                        $rightStyle = !empty($ticketImg) ? 'style="--ticket-bg-img: url(' . esc_url($ticketImg) . ');"' : '';
                        ?>
                        <<?php echo $ticketTag; ?> class="tb-ticket" <?php echo $ticketAttr; ?>>
                            <div class="tb-ticket-banner" style="background: <?php echo $bannerColor; ?>;">
                                <?php if (!empty($ticketIcon)): ?>
                                    <img src="<?php echo esc_url($ticketIcon); ?>" alt="Carrier" class="tb-ticket-banner-icon">
                                <?php endif; ?>
                            </div>
                            <div class="tb-ticket-left">
                                <div class="tb-ticket-route">
                                    <div class="tb-ticket-station">
                                        <div class="tb-ticket-station-name"><?php echo htmlspecialchars($ticket['from'] ?? ''); ?></div>
                                        <?php if (!empty($ticket['from_IATA'])): ?>
                                            <div class="tb-ticket-station-code"><?php echo htmlspecialchars($ticket['from_IATA']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tb-ticket-arrow"></div>
                                    <div class="tb-ticket-station">
                                        <div class="tb-ticket-station-name"><?php echo htmlspecialchars($ticket['to'] ?? ''); ?></div>
                                        <?php if (!empty($ticket['to_IATA'])): ?>
                                            <div class="tb-ticket-station-code"><?php echo htmlspecialchars($ticket['to_IATA']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="tb-ticket-info">
                                    <?php if (!empty($ticket['Date'])): ?>
                                        <div class="tb-ticket-info-item">
                                            <div class="tb-ticket-info-label">Date</div>
                                            <div class="tb-ticket-info-value"><?php echo htmlspecialchars($ticket['Date']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($ticket['goTime']) || !empty($ticket['arvTime'])): ?>
                                        <div class="tb-ticket-info-item">
                                            <div class="tb-ticket-info-label">Time</div>
                                            <div class="tb-ticket-info-value">
                                                <?php echo htmlspecialchars($ticket['goTime'] ?? ''); ?>
                                                <?php if (!empty($ticket['goTime']) && !empty($ticket['arvTime'])): ?>-<?php endif; ?>
                                                <?php echo htmlspecialchars($ticket['arvTime'] ?? ''); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($ticket['Number'])): ?>
                                        <div class="tb-ticket-info-item">
                                            <div class="tb-ticket-info-label">Flight/Train</div>
                                            <div class="tb-ticket-info-value"><?php echo htmlspecialchars($ticket['Number']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($ticket['Seat'])): ?>
                                        <div class="tb-ticket-info-item">
                                            <div class="tb-ticket-info-label">Seat</div>
                                            <div class="tb-ticket-info-value"><?php echo htmlspecialchars($ticket['Seat']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($ticket['Class'])): ?>
                                        <div class="tb-ticket-info-item">
                                            <div class="tb-ticket-info-label">Class</div>
                                            <div class="tb-ticket-info-value"><?php echo htmlspecialchars($ticket['Class']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($ticket['quote'])): ?>
                                    <div class="tb-ticket-quote-section">
                                        <div class="tb-ticket-quote-label">Quote</div>
                                        <div class="tb-ticket-quote-content">"<?php echo htmlspecialchars($ticket['quote']); ?>"</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="tb-ticket-divider"></div>
                            <div class="tb-ticket-right" <?php echo $rightStyle; ?>>
                                <?php if (!empty($ticket['weather']) || !empty($ticket['mood'])): ?>
                                    <div class="tb-ticket-meta">
                                        <?php if (!empty($ticket['weather'])): ?>
                                            <span><iconify-icon icon="fluent:weather-sunny-24-filled"></iconify-icon> <?php echo htmlspecialchars($ticket['weather']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($ticket['mood'])): ?>
                                            <span><iconify-icon icon="fluent:emoji-24-filled"></iconify-icon> <?php echo htmlspecialchars($ticket['mood']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($ticketStamp)): ?>
                                    <img src="<?php echo esc_url($ticketStamp); ?>" alt="Stamp" class="tb-ticket-stamp">
                                <?php endif; ?>
                            </div>
                        </<?php echo $ticketTag; ?>>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
