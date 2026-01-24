const { defineConfig } = require('@vue/cli-service')
const path = require('path')

function resolve(dir) {
  return path.join(__dirname, dir)
}

/**
 * 全局注入 scss 变量
 */
const globalSass = (config) => {
  const oneOfsMap = config.module.rule('scss').oneOfs.store
  oneOfsMap.forEach((item) => {
    item
      .use('sass-resources-loader')
      .loader('sass-resources-loader')
      .options({
        resources: './src/assets/common.scss'
      })
      .end()
  })
}

module.exports = defineConfig({
  /**
   * 🔥 关键配置：强制转译 node_modules 中的现代语法
   * 解决 Unexpected token '?' 白屏问题
   */
  transpileDependencies: [
    'vant',
    'vue-router',
    'vuex',
    'vue-i18n',
    'axios',
    /core-js/
  ],

  /**
   * 多域名 / 子路径部署安全写法
   */
  publicPath: process.env.NODE_ENV === 'production' ? './' : '/',

  /**
   * 静态资源目录
   */
  assetsDir: 'static',

  /**
   * 关闭 eslint 保存校验
   */
  lintOnSave: false,

  /**
   * 生产环境不生成 sourcemap
   */
  productionSourceMap: false,

  /**
   * 开发环境代理配置
   */
  devServer: {
    open: false,
    proxy: {
      '/vi': {
        target: 'https://admin.gnvcso.com',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/vi/, '/')
      }
    }
  },

  /**
   * webpack 链式配置
   */
  chainWebpack: (config) => {
    config.resolve.alias
      .set('@', resolve('src'))
      .set('assets', resolve('src/assets'))
      .set('components', resolve('src/components'))

    globalSass(config)
  },

  /**
   * 生产环境 webpack 配置
   */
  configureWebpack: (config) => {
    if (process.env.NODE_ENV === 'production') {
      config.mode = 'production'
      config.performance = {
        maxEntrypointSize: 10000000,
        maxAssetSize: 30000000
      }
    }
  },

  /**
   * style-resources-loader 插件配置
   */
  pluginOptions: {
    'style-resources-loader': {
      preProcessor: 'scss',
      patterns: []
    }
  }
})
