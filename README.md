非洲亚马逊抢单任务

方案组说明：
    抢单次数：当前方案组可以抢多少单，这个次数不参与会员登记次数限制。
    方案计划：在这个方案组开始做单后第几单触发，比如有3个计划，就分别在哪三单触发。默认模式就是根据设置的条件触发对应金额订单。叠加模式就是输入比例乘以用户余额就是触发到该订单时的金额。固定模式就是输入的金额加上用户的余额就是触发到该订单时的订单金额。触发等级就是当用户会员等级低于设置的等级的时候会给当前用户提升登记

    使用方法：在下面批量绑定用户或者到用户列表位置匹配方案组
    系统匹配订单模式优先级：单控 > 方案组 > 普通抢单
系统抢单说明：
    整个系统有三种模式：
    单控：在用户列表设置单控方案，佣金和订单数量以这里为准
    方案组：佣金有固定和按订单比例两种，以方案组内填写为准
    普通订单：佣金和订单金额以会员等级为准，会员等级编辑里面也是有固定佣金和按比例计算两种，订单匹配金额也是以这里的抢单最低金额和抢单最高金额为准，在这个范围内随机生成订单金额
    
    系统优先级是单控 > 方案组 > 普通订单
    
    只有不设置单控和方案组的情况下金额和佣金才以会员等级进行匹配

用户冻结金额以后台用户列表操作为准

代理规则：
    #处理前端接口
    location ~ ^/(vi|upload)/ {
        proxy_pass http://127.0.0.5;
        proxy_buffer_size 16k;
        proxy_buffers 4 32k;
        proxy_busy_buffers_size 64k;
        
        proxy_ssl_verify off;
        proxy_ssl_server_name on;
        
        proxy_connect_timeout 10s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
        
        proxy_set_header Host admin.gnvcso.com;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
    }
    #处理前端页面不能刷新的问题
    location / {
        try_files $uri $uri/ /index.html;
    }
