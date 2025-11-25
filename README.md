非洲亚马逊抢单任务

方案组说明：
    抢单次数：当前方案组可以抢多少单，这个次数不参与会员登记次数限制。
    方案计划：在这个方案组开始做单后第几单触发，比如有3个计划，就分别在哪三单触发。默认模式就是根据设置的条件触发对应金额订单。叠加模式就是输入比例乘以用户余额就是触发到该订单时的金额。固定模式就是输入的金额加上用户的余额就是触发到该订单时的订单金额。触发等级就是当用户会员等级低于设置的等级的时候会给当前用户提升登记

    使用方法：在下面批量绑定用户或者到用户列表位置匹配方案组
    系统匹配订单模式优先级：单控 > 方案组 > 普通抢单

代理规则：
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
    location / {
        try_files $uri $uri/ /index.html;
    }