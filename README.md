# q-dingtk

> A Vue.js project

## Build Setup

``` bash
# install dependencies
npm install

# serve with hot reload at localhost:8080
npm run dev

# build for production with minification
npm run build

# build for production and view the bundle analyzer report
npm run build --report

# run unit tests
npm run unit

# run e2e tests
npm run e2e

# run all tests
npm test
```

//在生产环境为提高性能、访问效率
'routerCacheFile' => __DIR__ . '/../../routes.cache.php',
该文件包含一个关联数组，该数据表示路由器不需要重新编译它所使用的正则表达式。

数组详解：
路由定义请求方式：（get， post。。。）
句柄
路由匹配正则
接受参数

注意：
此缓存永久有效，如有路由添加或改变需要删除此文件重新生成
so，比较适合应用于生产环境

//常用库的使用
illuminate/database 一个 ORM 的类库，有点强

Respect/Validation

Slim-Flash
