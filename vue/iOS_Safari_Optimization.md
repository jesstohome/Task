# iOS Safari 滚动性能优化指南

## 问题分析

在 iPhone Safari 上遇到的滚动卡顿问题，主要原因：

1. **BetterScroll 库在 Safari 上性能欠佳** - JS 模拟滚动不如原生滚动流畅
2. **缺少 iOS 特定的 CSS 优化** - 没有启用硬件加速
3. **固定定位元素干扰** - Footer 的 `position: fixed` 可能影响滚动流畅度

## 已应用的修复

### 1. 更新 `scroll.vue` - 改用原生滚动

**改动点：**
- 移除了 BetterScroll 库
- 使用原生的 `overflow: auto` 和 `-webkit-overflow-scrolling: touch`
- 添加原生滚动事件监听

**关键 CSS：**
```css
#wrapper {
  height: 100%;
  overflow: auto;
  -webkit-overflow-scrolling: touch;  /* 启用 iOS 原生滚动动力学 */
}
```

### 2. 优化 `footer.vue` - 硬件加速

**改动点：**
- 添加 `-webkit-transform: translateZ(0)` 强制硬件加速
- 添加 `will-change: transform` 告知浏览器准备变换

```css
.footer {
  -webkit-transform: translateZ(0);  /* iOS 硬件加速 */
  transform: translateZ(0);
  will-change: transform;
}
```

### 3. 优化 `App.vue` - 容器设置

**改动点：**
- 容器设置为 `position: fixed` 和 `overflow: hidden`
- 添加 `-webkit-user-select: none` 防止文本选中导致的卡顿
- 添加 `touch-action: pan-y` 优化触摸响应

```css
#app {
  position: fixed;
  width: 100%;
  overflow: hidden;
  -webkit-user-select: none;
  user-select: none;
  touch-action: pan-y;  /* 只允许纵向滚动 */
}
```

## 进一步优化建议

如果还有卡顿，可以尝试以下额外措施：

### 方案 A: 使用 `overflow: scroll` 而不是 `auto`
```css
#wrapper {
  height: 100%;
  overflow-y: scroll;  /* 强制显示滚动条（iOS会自动隐藏） */
  -webkit-overflow-scrolling: touch;
}
```

### 方案 B: 增加虚拟滚动（如果列表很长）
在列表组件中使用虚拟滚动库，只渲染可见区域内的元素：
- `vue-virtual-scroll`
- `vue-virtual-scroller`
- Vant 的 `List` 组件已内置虚拟滚动

### 方案 C: 减少重排（Reflow）
```css
/* 使用 transform 而不是 top/left 移动元素 */
.element {
  transform: translateX(0);  /* 比改 left 性能好 */
}

/* 避免在滚动时修改样式 */
/* 不要在 scroll 事件中频繁更新 DOM */
```

### 方案 D: 防抖滚动事件
```javascript
methods: {
  onScroll: debounce(function(e) {
    this.handleToScroll({
      x: -e.target.scrollLeft,
      y: -e.target.scrollTop
    })
  }, 100)
}
```

## 性能测试建议

1. **在真实 iPhone 设备上测试**（模拟器表现不同）
2. **使用 Safari DevTools** - 连接 Mac 的 Safari，打开 iOS 设备的 Web Inspector
3. **检查 Frame Rate** - 应该保持 60fps，iPhone ProMotion 设备支持 120fps
4. **检查内存使用** - 使用 DevTools 的 Memory 标签

## 常见陷阱

❌ **不要这样做：**
```css
/* 会导致卡顿 */
position: absolute;  /* 频繁重排 */
box-shadow: 0 0 10px rgba(0,0,0,0.5);  /* 性能开销大 */
filter: drop-shadow();  /* iOS 上性能差 */
border-radius: 50%;  /* 多个半径组合 */
```

✅ **应该这样做：**
```css
/* 性能优化 */
transform: translateZ(0);  /* 硬件加速 */
will-change: transform;     /* 告知优化 */
backface-visibility: hidden;  /* 禁用背面 */
perspective: 1000px;        /* 3D 优化 */
```

## 更新日志

- **v1.0** (2025-11-13)
  - 移除 BetterScroll，改用原生滚动
  - 添加 iOS Safari 硬件加速优化
  - 优化 Footer 和 App 容器设置
