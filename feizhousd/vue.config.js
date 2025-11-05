const { defineConfig } = require('@vue/cli-service')
const path = require('path')

function resolve(dir) {
  return path.join(__dirname, dir)
}

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: process.env.NODE_ENV === 'production' ? './' : '/',
  assetsDir: 'static',
  lintOnSave: false,
  productionSourceMap: false,
  devServer: { // 设置代理
    open: false, //自动打开浏览器
    proxy: {
      '/vi': { 
        target: 'https://admin.gnvcso.com', // 代理的线上的接口地址
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/vi/, "/")
      }
    }
  },
  chainWebpack: config => {
    config.resolve.alias
      .set('@', resolve('src'))
      .set('assets', resolve('src/assets'))
      .set('components', resolve('src/components'))

      globalSass(config)
  },
  configureWebpack: (config) => {
    if (process.env.NODE_ENV === 'production') {// 为生产环境修改配置...
      config.mode = 'production';
      config["performance"] = {//打包文件大小配置
        "maxEntrypointSize": 10000000,
        "maxAssetSize": 30000000
      }
    }
  },
  pluginOptions: {
    'style-resources-loader': {
        preProcessor: 'scss',
        patterns: []
    }
}
})

/**
 * 注意Dependencies需要引入
 * "sass": "^1.32.7",
 *"sass-loader": "^12.0.0",
 *"sass-resources-loader": "^2.2.5"
 * 全局变量的Sass引方法，值得收藏
 * @param config  chainWebpack(config) 中来的一个配置
 */
 const globalSass = config => {
  const oneOfsMap = config.module.rule('scss').oneOfs.store
  oneOfsMap.forEach((item) => {
      item
          .use('sass-resources-loader')
          .loader('sass-resources-loader')
          .options({
              resources: './src/assets/common.scss'  //相对路径
          })
          .end()
  })
}