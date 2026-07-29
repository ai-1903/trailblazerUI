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
 * TrailblazerUI — WordPress 连接器
 * ====================================
 * 在你的 WordPress 子主题 functions.php 中加入以下代码即可挂载短代码：
 *
 *   require_once get_stylesheet_directory() . '/AirDesign/trailblazerUI/func/wp_connector.php';
 *
 * 之后在任意页面/文章中使用短代码：[my_trailblaze]
 */

// ---- 安全防范 -----------------------------------------------------------
if (!defined('ABSPATH')) {
    exit;
}

// ---- 加载共享渲染函数 ---------------------------------------------------
require_once __DIR__ . '/render.php';

// ---- 计算资源路径（相对于当前文件）---------------------------------------
$wp_connector_base_dir  = dirname(__DIR__);                                   // trailblazerUI/
$wp_connector_base_url  = get_stylesheet_directory_uri() . '/AirDesign/trailblazerUI'; // WordPress URI

// =========================================================================
// 1. 样式注册与引入
// =========================================================================
add_action('wp_enqueue_scripts', function () use ($wp_connector_base_url) {

    // ---- 调色盘变量（最先注册以保证优先级）------------------------------
    wp_enqueue_style(
        'adt-color-board',
        $wp_connector_base_url . '/css/color-board.css',
        [],
        '2.0.0'
    );

    // ---- Trailblaze UI 主样式 ------------------------------------------
    wp_enqueue_style(
        'adt-trailblaze-style',
        $wp_connector_base_url . '/css/trailblaze.css',
        ['adt-color-board'],
        '2.0.0'
    );

    // ---- Blocksy 专属补丁（仅 WordPress）-------------------------------
    wp_enqueue_style(
        'adt-core-mode',
        $wp_connector_base_url . '/css/core-mode.css',
        ['adt-trailblaze-style'],
        '2.0.0'
    );

    // ---- 动态注入 Blocksy 宽度限制补丁 ---------------------------------
    $blocksy_patch = "
    .tb-page-wrapper {
        margin-top: 40px;
        margin-bottom: 60px;
        width: var(--theme-container-width, calc(100% - 40px));
        max-width: var(--theme-block-max-width, 1200px);
        margin-inline: auto;
        box-sizing: border-box;
    }
    ";
    wp_add_inline_style('adt-trailblaze-style', $blocksy_patch);

    // ---- Iconify 图标库（异步加载，不阻塞首屏）--------------------------
    wp_enqueue_script(
        'iconify-icon',
        'https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js',
        [],
        '2.1.0',
        true
    );
});

// =========================================================================
// 2. 注册短代码 [my_trailblaze]
// =========================================================================
add_shortcode('my_trailblaze', function () use ($wp_connector_base_dir, $wp_connector_base_url) {

    // ---- 加载数据配置 ---------------------------------------------------
    $configJsonPath = $wp_connector_base_dir . '/data/trailblaze.json';

    // 兜底：如果原始路径不存在，尝试子主题根目录
    if (!file_exists($configJsonPath)) {
        $configJsonPath = get_stylesheet_directory() . '/trailblaze.json';
    }

    $tbData = file_exists($configJsonPath)
        ? json_decode(file_get_contents($configJsonPath), true)
        : [];

    // ---- 主题检测 -------------------------------------------------------
    $themeMode = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

    // ---- 渲染 -----------------------------------------------------------
    return trailblazer_render_html($tbData, $themeMode, $wp_connector_base_url);
});
