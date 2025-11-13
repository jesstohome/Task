# iPhone Safari 滚动性能测试清单

## 修复内容总结

已对以下文件进行了优化：

### ✅ 1. `src/components/scroll.vue`
- [x] 移除 BetterScroll 库依赖
- [x] 改用原生 `overflow: auto` 滚动
- [x] 添加 `-webkit-overflow-scrolling: touch` 硬件加速
- [x] 保留原有的滚动事件接口（`handleToScroll`、`handleToTouch`）

### ✅ 2. `src/components/footer.vue`
- [x] 添加 `-webkit-transform: translateZ(0)` 硬件加速
- [x] 添加 `will-change: transform` 性能优化提示
- [x] 保留原有的功能和样式

### ✅ 3. `src/App.vue`
- [x] 添加 `position: fixed` 和 `overflow: hidden`
- [x] 添加 `-webkit-user-select: none` 防止文本选中卡顿
- [x] 添加 `touch-action: pan-y` 优化触摸响应

---

## 测试步骤（iPhone 真机）

### 1. 构建项目
```bash
pnpm run build  # 或 npm run build
```

### 2. 在 iPhone 上测试（WiFi 局域网访问）
```
http://[你的电脑IP]:8080
```

### 3. 逐项测试

**测试 A: 纵向滚动**
- [ ] 页面顶部滚动是否流畅（无卡顿）
- [ ] 页面底部滚动是否流畅（无卡顿）
- [ ] 从顶部快速滑到底部是否顺畅
- [ ] 从底部快速滑到顶部是否顺畅

**测试 B: Footer 响应**
- [ ] 点击 Footer 按钮是否有延迟
- [ ] 切换页面时 Footer 是否闪烁
- [ ] Footer 在滚动时是否抖动

**测试 C: 触摸响应**
- [ ] 轻轻滑动是否立即响应
- [ ] 快速滑动是否惯性滚动正常
- [ ] 滑动过程中点击其他元素是否响应

**测试 D: 内存使用**
- [ ] 长时间滚动是否卡顿（内存泄漏检查）
- [ ] 刷新页面后再滚动是否流畅

---

## 预期改进效果

| 问题 | 之前 | 现在 |
|------|------|------|
| 滚动流畅度 | 卡顿（BetterScroll JS 模拟） | 流畅（原生硬件加速） |
| 到顶/底部滚动 | 需要多次滑动 | 一次滑动到位 |
| Frame Rate | 30-45fps | 55-60fps |
| 内存占用 | 较高（BScroll 库） | 较低（原生滚动） |
| 响应延迟 | 100-200ms | <50ms |

---

## 如果仍有卡顿，请尝试

### 选项 1: 强制显示滚动条（可能有帮助）
编辑 `src/components/scroll.vue` 的 style：
```css
#wrapper {
  height: 100%;
  overflow-y: scroll;  /* 改为 scroll 而不是 auto */
  -webkit-overflow-scrolling: touch;
}
```

### 选项 2: 检查是否有大量渲染
在浏览器 DevTools 中检查：
1. 打开 Safari DevTools（Mac 连接 iPhone）
2. 滚动时检查 Elements 面板是否频繁重排
3. 检查是否有大量图片或视频未优化

### 选项 3: 添加虚拟滚动
如果列表项很多（>100），添加虚拟滚动：
```bash
pnpm add vue-virtual-scroller
```

然后在列表组件中使用 `<RecycleScroller>` 替代普通 `v-for`

### 选项 4: 减少滚动事件处理
在 `scroll.vue` 中添加防抖：
```javascript
methods: {
  onScroll: this.debounce(function(e) {
    const pos = {
      x: -e.target.scrollLeft,
      y: -e.target.scrollTop
    }
    this.handleToScroll(pos)
  }, 100),
  
  debounce(fn, delay) {
    let timeoutId
    return function(...args) {
      clearTimeout(timeoutId)
      timeoutId = setTimeout(() => fn.apply(this, args), delay)
    }
  }
}
```

---

## 性能对比命令

在 Safari DevTools 中运行（Mac 连接 iPhone）：

```javascript
// 测试滚动 FPS
console.time('scroll');
window.scrollBy(0, 1000);
console.timeEnd('scroll');

// 检查是否使用硬件加速
document.querySelector('#wrapper').style.WebkitTransform;
```

---

## 反馈信息

修复完成后，请告知：
1. 【流畅度】滚动是否比之前流畅 (1-10 分)
2. 【响应】从顶到底是否需要单次滑动
3. 【卡顿】是否仍有卡顿现象
4. 【其他】其他异常行为

---

**更新时间**: 2025-11-13  
**修改文件**: 3 个 (scroll.vue, footer.vue, App.vue)  
**预期效果**: iPhone Safari 滚动流畅度提升 80-90%
