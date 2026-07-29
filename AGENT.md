# AGENT.md — trailblazerUI 项目知识库

> 自动生成于 2026-07-29，基于完整目录遍历和源代码分析。

---

## 项目概览

| 项目 | 详情 |
|---|---|
| **名称** | trailblazerUI |
| **定位** | 个人"开拓之旅"展示组件 — 一个独立、可嵌入的 PHP UI Kit |
| **作者** | iCerya (icerya.com) |
| **许可** | Apple Public Source License 2.0 |
| **设计风格** | Apple 美学 — 大圆角、毛玻璃、SF 字体、灵动岛胶囊、果味渐变 |

---

## 目录结构

```
trailblazerUI/
├── css/
│   ├── color-board.css      # 设计令牌：调色盘变量 + 亮/暗模式
│   ├── trailblaze.css       # 主 UI 样式（Hero、Dashboard、卡片、响应式）
│   └── core-mode.css        # WP Blocksy 主题兼容补丁 + 自定义元素
├── func/
│   ├── render.php           # 共享渲染引擎（HTML 生成、进度计算、图片路径）
│   ├── connector.php        # 独立 PHP 连接器（非 WP 环境一键引入）
│   └── wp_connector.php     # WordPress 连接器（短代码 [my_trailblaze]）
├── data/
│   └── trailblaze.json      # 数据配置（统计、已解锁城市、下一站计划）
├── README.md
├── LICENSE
└── .gitignore
```

---

## CSS 层详解

### 1. `color-board.css` — 设计令牌层
- 定义 `--adt-colorBoard-*` 系列 CSS 变量（Red/Orange/Yellow/Green/Mint/Teal/Cyan/Blue/Indigo/Purple/Pink/Brown）
- 存储为 RGB 分量（如 `255 59 48`），配合 `rgba(var(--x) / alpha)` 使用
- 定义 `--adt-colorAlpha-*` 透明度层级（3% ~ 100%，共 12 级）
- 通过 `[data-color-mode="dark"]` 切换深色模式色值
- 全局动效变量 `--adt-ease-out-expo`

### 2. `trailblaze.css` — 主 UI 层
- **Hero 区域**：`.tb-hero` + `.tb-title`（48px/900 字重/负字间距）
- **进度条**：iOS 健身环风格，flex 布局，绿色渐变填充 + `cubic-bezier` 过渡动画，SF Mono 百分比显示
- **Dashboard 大卡片**：32px 圆角、`backdrop-filter: blur(40px)`、悬浮大阴影、flex 双栏布局
- **灵动岛胶囊** `.tb-pill`：40px 圆角、黑色背景白色文字、带阴影的 iOS 风格 badge
- **统计区**：64px 超大数字 + 单位/描述，蓝色距离 pill
- **城市标签**：12px 圆角方块、hover 缩放效果
- **下一站封面**：带 hover 图片放大动画
- **计划卡片**：三态（默认/active 高亮蓝色），active 态上浮 + 投影
- **响应式**：850px 断点，卡片改为纵向、数字缩小至 48px

### 3. `core-mode.css` — WP Blocksy 兼容层
- 超级主题标题渐变动画（`masked-animation` keyframes，青→粉循环）
- Blocksy 毛玻璃效果（header sticky、shortcuts bar、data-block 113）
- APlayer 播放器响应式适配
- 行内代码样式（圆角、背景色变量）
- 页脚链接下划线控制
- 表格边框颜色修复
- 分割线 `<hr>` 颜色
- 侧边栏宽度（已注释，备用）
- 已解耦移除：滚动条样式、Cookie 通知栏样式

---

## PHP 层详解

### `render.php` — 渲染引擎（共享核心）
**关键函数**：

| 函数 | 用途 |
|---|---|
| `trailblazer_calc_progress()` | 计算年度进度（已过天数/剩余天数/百分比），自动处理闰年 |
| `trailblazer_smart_img($url, $theme, $baseUrl)` | 智能图片路径：自动补全绝对路径 + 深色模式下探测 `-dark` 后缀（SVG 跳过） |
| `trailblazer_render_html($data, $themeMode, $baseUrl)` | 主渲染函数，输出完整 HTML（Hero + Dashboard 卡片） |
| `esc_html()` / `esc_url()` polyfill | 独立 PHP 环境下的 XSS 防护兼容层 |

**输出结构**（`trailblazer_render_html`）：
```
.tb-page-wrapper
  ├── .tb-hero (标题 "愿此行，终抵群星。" + 年度进度条)
  └── .tb-dashboard-card
        ├── .tb-dash-left (统计数字 + 已解锁城市 pills)
        └── .tb-dash-right (下一站封面 + 计划卡片)
```

### `connector.php` — 独立模式连接器
- 防重复加载：`TRAILBLAZER_LOADED` 常量守卫
- 自动检测资源 URL（基于 `DOCUMENT_ROOT` 计算相对路径）
- 零配置：只需 `include 'connector.php'` 即可渲染
- 高级用法：`define('TRAILBLAZER_BASE_URL', '/custom/path')` 手动指定路径
- 主题检测：读取 `$_COOKIE['theme']`，默认 `light`
- 内联输出 CSS（去除注释以减小体积）
- 加载 Iconify CDN 脚本

### `wp_connector.php` — WordPress 连接器
- 挂载到子主题 `functions.php`：`require_once get_stylesheet_directory() . '/AirDesign/trailblazerUI/func/wp_connector.php'`
- 注册短代码 `[my_trailblaze]`
- 通过 `wp_enqueue_scripts` action 注册样式（color-board → trailblaze → core-mode 依赖链）
- 动态注入 Blocksy 容器宽度补丁（`wp_add_inline_style`）
- 深色模式继承 WordPress Cookie `theme`

---

## 数据模型 (`data/trailblaze.json`)

```json
{
  "stats": {
    "cities": 13,        // 已访问城市数
    "countries": 1,      // 已访问国家数
    "distance": "23,500" // 累计里程(km)
  },
  "unlockedCities": [],  // 已解锁城市名称数组
  "nextDestinations": {
    "cover": "/img/trailblaze/next-cover.jpg",  // 下一站封面图
    "tag": "大地纪行",                            // 标签文案
    "plans": [
      {
        "country": "中国香港",
        "cities": "错位 · 金融中心",
        "isActive": false   // 当前高亮计划
      }
    ]
  }
}
```

---

## 技术要点

1. **双重环境兼容**：同一套 PHP 代码同时支持独立 PHP 页面和 WordPress 短代码
2. **CSS 变量体系**：颜色全部通过 `rgba(var(--x) / alpha)` 调用，天然支持亮/暗模式切换
3. **零外部依赖**：独立模式下不需要任何框架，仅需 PHP 7+ 即可运行
4. **性能优化**：
   - CSS 内联输出（减少 HTTP 请求）
   - Iconify 脚本 `defer` 加载
   - 去除 CSS 注释以减小体积
   - 防重复加载守卫
5. **安全性**：`esc_html`/`esc_url` 输出转义，XSS 防护
6. **设计系统**：完整的 Apple 风格设计语言 — 毛玻璃、大圆角、SF 字体、弹性动画曲线

---

## 当前状态

- **已解锁城市**：13 个（哈尔滨、上海、重庆、天津滨海、成都、海口、三亚、三沙、厦门、杭州、西安、银川 + 1 个）
- **下一站计划**：中国香港、美国加州库比蒂诺（Apple Park）
- **深色模式**：支持，通过 CSS 变量自动切换
