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
 * TrailblazerUI — 独立 PHP 连接器
 * ====================================
 * 将此文件包含在任意 PHP 页面中即可渲染开拓之旅界面。
 *
 * 用法：
 *   <?php include 'path/to/trailblazerUI/func/connector.php'; ?>
 *
 * 高级（手动指定资源 URL）：
 *   <?php
 *   define('TRAILBLAZER_BASE_URL', '/my-assets/trailblazerUI');
 *   include 'path/to/trailblazerUI/func/connector.php';
 *   ?>
 *
 * 注意：
 *   - 本文件会内联输出 CSS 和必要的 <script> 标签
 *   - 如需最佳性能，建议在 <head> 中引入或在调用前定义 TRAILBLAZER_BASE_URL
 *   - 同一页面多次 include 仅会执行一次（通过 TRAILBLAZER_LOADED 守卫）
 */

// ---- 防重复加载 ---------------------------------------------------------
if (defined('TRAILBLAZER_LOADED')) {
    return;
}
define('TRAILBLAZER_LOADED', true);

// ---- 基础路径 -----------------------------------------------------------
define('TRAILBLAZER_BASE', dirname(__DIR__));

// ---- 加载共享渲染函数 ---------------------------------------------------
require_once __DIR__ . '/render.php';

// ---- 加载数据配置 -------------------------------------------------------
$tb_json_path = TRAILBLAZER_BASE . '/data/trailblaze.json';
$tb_data = file_exists($tb_json_path)
    ? json_decode(file_get_contents($tb_json_path), true)
    : [];

// ---- 主题检测（Cookie 驱动，与 WordPress 主题兼容）-----------------------
$themeMode = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// ---- 资源 URL 自动检测 -------------------------------------------------
if (!defined('TRAILBLAZER_BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $tbPath  = str_replace('\\', '/', TRAILBLAZER_BASE);
    $baseUrl = str_replace($docRoot, '', $tbPath);
    define('TRAILBLAZER_BASE_URL', $baseUrl);
}

// ---- 内联 CSS（零配置，即插即用）----------------------------------------
$strip_css_comments = function($css) {
    return preg_replace('/\/\*.*?\*\//s', '', $css);
};

// 调色盘变量（必须最先加载）
$colorBoardCss = @file_get_contents(TRAILBLAZER_BASE . '/css/color-board.css');
if ($colorBoardCss) {
    echo '<style id="trailblazer-color-board">' . $strip_css_comments($colorBoardCss) . '</style>';
}

// 缺失的 WP/主题变量兜底（使独立模式不依赖外部主题）
echo '<style id="trailblazer-fallback">
:root {
    --card-bg: rgba(255,255,255,0.72);
    --text-secondary: rgba(var(--adt-colorBoard-Label), 0.5);
    --color-blue: #007AFF;
    --color-blue-rgb: 0 122 255;
    --nav-height: 0px;
}
[data-color-mode="dark"] {
    --card-bg: rgba(30,30,32,0.85);
    --text-secondary: rgba(var(--adt-colorBoard-Label), 0.55);
}
</style>';

// 主 UI 样式
$trailblazeCss = @file_get_contents(TRAILBLAZER_BASE . '/css/trailblaze.css');
if ($trailblazeCss) {
    echo '<style id="trailblazer-ui">' . $strip_css_comments($trailblazeCss) . '</style>';
}

// ---- Iconify 图标库 ----------------------------------------------------
echo '<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js" defer></script>';

// ---- 渲染界面 -----------------------------------------------------------
echo trailblazer_render_html($tb_data, $themeMode, TRAILBLAZER_BASE_URL);
