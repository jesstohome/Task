FROM php:8.1-fpm

WORKDIR /var/www/html

# 【关键修复】添加了 libonig-dev，这是 mbstring 必须的依赖
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 安装扩展
# 建议去掉 -j$(nproc) 以保证稳定性，虽然现在内存够了，但单线程更不容易出奇怪错误
RUN docker-php-ext-install pdo pdo_mysql mbstring zip

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]


# ThinkPHP 需要写入 runtime 和 public/static 等目录。由于容器内用户是 www-data (ID 33)，而你是用 root 或普通用户启动的，可能会报 Permission denied。
# 进入 php 容器
# docker exec -it car_php bash

# # 在容器内执行 (假设项目根目录在 /var/www/html)
# chown -R www-data:www-data /var/www/html
# chmod -R 775 /var/www/html/runtime
# chmod -R 775 /var/www/html/public

# # 退出
# exit

# 或者在宿主机直接执行（如果宿主机是 Linux/Mac）：
# sudo chown -R 33:33 ./houtai