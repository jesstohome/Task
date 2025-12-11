import { createVNode, render } from "vue";
import messageComponent from './message.vue'
let mountNode = null;
let count = 0 // 定义统计次数，便于知道创建多少个实例import { createVNode, render } from "vue";
export default (options) => {
  const duration = options.duration | 2000;
  //确保只存在一个弹框，如果前一个弹窗还在，就移除
  if (mountNode) {
    document.body.removeChild(mountNode);
    mountNode = null;
  }
  //将options参数传入，并将Notice组件转换成虚拟DOM，并赋值给app
  const app = createVNode(messageComponent, {
    data: options, // data传参数，组件的data接收（即传递配置项）
    propsData: { // propsData传参，
        count: count, // 将统计的次数传递给子组件
        cutCount: cutCount // 传递一个函数，当MyMessage消失的时候，通知外界
    },
  });
  //创建定时器，duration时间后将mountNode移除
  let timer = setTimeout(() => {
    document.body.removeChild(mountNode);
    mountNode = null;
    clearTimeout(timer);
  }, duration);
  //创建一个空的div
  mountNode = document.createElement("div");
  //render函数的作用就是将Notice组件的虚拟DOM转换成真实DOM并插入到mountNode元素里
  render(app, mountNode);
  //然后把转换成真实DOM的Notice组件插入到body里
  document.body.appendChild(mountNode);
};
function cutCount() { // 当message消失一个
    count = count - 1 // 就把外界统计的数量减少一个
    let messageBoxDomList = document.querySelectorAll('.messageBox') // 然后选中所有的messageDOM元素
    for (let i = 0; i < messageBoxDomList.length; i++) { // 遍历一下这个DOM伪数组
        let dom = messageBoxDomList[i] // 所有的都往上移动60像素
        dom.style['top'] = parseInt(dom.style['top']) - 60 + 'px'
    }
}