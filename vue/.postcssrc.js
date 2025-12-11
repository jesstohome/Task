module.exports = {
  plugins: {
    autoprefixer: {}, // 用来给不同的浏览器自动添加相应前缀，如-webkit-，-moz-等等
    "postcss-pxtorem": {
      rootValue: 37.5, // 根字体大小，750设计稿/20 = 37.5
      unitPrecision: 6, // 转换后的精度，即小数点位数
      propList: ["*"], // 指定转换的css属性的单位，*代表全部css属性的单位都进行转换
      selectorBlackList: ["wrap"], // 指定不转换为rem单位的类名
      replace: true, // 是否转换后直接更换属性值
      mediaQuery: true, // 是否在媒体查询的css代码中也进行转换
      minPixelValue: 1, // 默认值1，小于或等于1px则不进行转换
      exclude: /node_modules/i // 排除node_modules目录
    }
  }
};
