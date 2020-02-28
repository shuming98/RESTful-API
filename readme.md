## API开发：RESTful API

## 设计要素：资源路径、HTTP动词、过滤信息、状态码、错误处理、返回结果

## 开发环境：从 <www.upupw.net> 网站下载apache/nginx环境包。
- 添加虚拟主机：运行upupw.exe程序，输入“s1”——>开启所有服务——>“1”——>输入虚拟主机“api.test”——>回车。
- 配置虚拟主机：
    + 修改apache的vhost.conf文件，注释掉“php_admin...”该行，并重启服务。
    + 修改C:\Windows\System32\drivers\etc\hosts文件,添加一行：127.0.0.1 api.test,保存退出。

## 项目目录

    /api                    #根目录
        /lib
            db.php          #数据库句柄
            User.php        #User类
            Article.php     #Article类
            ErrorCode.php   #错误代码
        /restful
            .htaccess       #url重写
            index.php       #入口文件，用户&文章API


## 数据库
- 引入文件：mysql -uroot -p api < api.sql
- 配置数据库： /lib/db.php

## 调试工具：Chrome安装Restlet client 或 postman扩展。

## 调试
- URI
    * 用户注册[POST]：xxx/users
    * 文章创建[POST]：xxx/articles
    * 文章编辑[PUT]：xxx/articles/id
    * 文章删除[DELETE]：xxx/articles/id
    * 查看文章[GET]:xxx/articles/id
    * 文章列表[GET]：xxx/articles

- 用户登陆：需添加头信息：Authorization:Basic (base64)username:password
- body：{"":"","":""}

