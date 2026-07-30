<div align="center">

# trailblazerUI

**An MT Kit showcasing a personal trailblaze journey.**

一个独立、可嵌入的 PHP UI Kit，以 Apple 设计美学呈现个人旅行足迹仪表盘。

[![License](https://img.shields.io/badge/license-APSL--2.0-orange.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.0+-purple.svg)](https://www.php.net/)
</div>

## MT Note

> [!CAUTION]
> 本项目使用 Apple Public Source License 2.0，修改或衍生作品须同样以 APSL-2.0 开源；若发起涉及本代码的专利诉讼，许可证将自动终止。

---

## 特性

- **🍎 Apple 美学** — 毛玻璃大卡片、灵动岛胶囊、SF Mono 数字、果味渐变进度条
- **🎫 旅行车票** — 精美车票样式卡片，半圆裁切、虚线分隔、承运商 Banner
- **🌗 亮/暗双模式** — CSS 变量驱动，无缝适配系统主题
- **📊 数据驱动** — JSON 配置文件，更新数据无需改代码
- **🔌 即插即用** — 独立 PHP 页面只需一行 `include`，WordPress 支持短代码 `[my_trailblaze]`
- **📱 响应式** — 850px 断点自适应移动端
- **🪶 零框架依赖** — 仅需 PHP 7+

---

## 目录结构

```
trailblazerUI/
├── css/
│   ├── color-board.css      # 设计令牌：色板变量 + 亮/暗模式
│   └── trailblaze.css       # 主 UI 样式（Dashboard + 车票模块）
├── func/
│   ├── render.php           # 渲染引擎
│   ├── connector.php        # 独立 PHP 连接器
│   └── wp_connector.php     # WordPress 连接器
├── data/
│   └── trailblaze.json      # 行程数据配置
├── img/
│   └── ...                  # 图片资源（封面、图标、印章等）
├── index.php                # Demo 入口
├── README.md
└── LICENSE
```

---

## Demo 开箱即用

```bash
cd trailblazerUI
php -S localhost:8080
```

浏览器打开 `http://localhost:8080` 即可预览完整 Demo。

Demo 入口 `index.php` 内置亮/暗模式切换、1200px 内容宽度限制，开箱零配置。

---

## 快速开始

### 独立 PHP 环境

```php
<?php include 'path/to/trailblazerUI/func/connector.php'; ?>
```

如需自定义资源路径：

```php
<?php
define('TRAILBLAZER_BASE_URL', '/custom/assets/trailblazerUI');
include 'path/to/trailblazerUI/func/connector.php';
?>
```

### WordPress

在子主题 `functions.php` 中添加：

```php
require_once get_stylesheet_directory() . '/AirDesign/trailblazerUI/func/wp_connector.php';
```

然后在任意页面或文章中使用短代码：

```
[my_trailblaze]
```

---

## 数据配置

编辑 `data/trailblaze.json` 即可更新展示内容：

### 整体结构

```json
{
  "stats": { "cities": 8, "countries": 3, "distance": "42,180" },
  "unlockedCities": [ "日本 东京", "韩国 首尔", "..." ],
  "nextDestinations": { "cover": "/img/next-cover.jpg", "tag": "星海征途", "plans": [...] },
  "tickets": [ ... ]
}
```

### Dashboard 数据

| 字段 | 类型 | 说明 |
|---|---|---|
| `stats.cities` | number | 已访问城市数 |
| `stats.countries` | number | 已访问国家数 |
| `stats.distance` | string | 累计里程（km） |
| `unlockedCities[]` | string | 已解锁城市名称 |
| `nextDestinations.cover` | string | 下一站封面图路径 |
| `nextDestinations.tag` | string | 标签文案（同时也是车票模块标题） |
| `nextDestinations.plans[].country` | string | 计划目的地名称 |
| `nextDestinations.plans[].cities` | string | 目的地描述 |
| `nextDestinations.plans[].isActive` | boolean | 是否高亮当前卡片 |

### 车票模块

```json
{
  "tickets": [
    {
      "from": "北京首都T3",
      "to": "哈尔滨太平 T2",
      "from_IATA": "PEK",
      "to_IATA": "HRB",
      "Number": "CA-1603",
      "Seat": "25L",
      "Class": "P",
      "Date": "2024-10-06",
      "goTime": "10:00",
      "arvTime": "12:00",
      "icon": "/img/gh.svg",
      "color": "34 77 155 / 1",
      "weather": "晴天",
      "mood": "不舍",
      "quote": "一段旅程，一段记忆。",
      "Stamp": "/img/stamp.svg",
      "link": "https://example.com"
    }
  ]
}
```

| 字段 | 类型 | 说明 |
|---|---|---|
| `from` / `to` | string | 出发 / 到达站点名 |
| `from_IATA` / `to_IATA` | string | IATA 代码，空则不显示 |
| `Number` | string | 航班 / 车次号 |
| `Seat` | string | 座位号 |
| `Class` | string | 舱位 / 席别 |
| `Date` | string | 日期 |
| `goTime` / `arvTime` | string | 出发 / 到达时间 |
| `icon` | string | 承运商 logo 路径（Banner 左侧小图标） |
| `color` | string | Banner 背景色（rgba 格式，如 `"34 77 155 / 1"`） |
| `weather` | string | 当日天气 |
| `mood` | string | 心情标签 |
| `quote` | string | 旅行引言 |
| `Stamp` | string | 印章图片路径 |
| `link` | string | 点击卡片跳转链接，空则为不可点击 |

> 所有字段均可为空，卡片不会因数据缺失而异常显示。

---

## 深色模式

自动读取 Cookie `theme`（值为 `dark` 时启用深色模式）。

在 WordPress + Blocksy 主题环境下，深色模式与主题设置自动同步，无需额外配置。

---

## 许可

Apple Public Source License 2.0 — 详见 [LICENSE](LICENSE)。

---

<p align="center">
  <sub>Made with ❤️ by <a href="https://icerya.com">iCerya</a></sub>
</p>
