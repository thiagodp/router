# Server configuration

## PHP built-in server

> Do not use it in a production environment ⚠

Steps:
1. Create the file `index.php`. It will be your API's entry point;
2. Run the following command in the folder where   `index.php` is located at:
    ```bash
    php -S localhost:80
    ```

Note: You may change the server port as you wish.

## Apache

> This documentation does not cover [Apache server configuration](https://ubuntu.com/tutorials/install-and-configure-apache)

1. In your project's folder, create the file `index.php`. It will be your API's entry point;
2. In the same folder as your `index.php`, create the file `.htaccess` with the following content:
    ```apache
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
    ```

### Nginx

> You may want to read [Setting Up a Proxy Server](https://www.nginx.com/blog/setting-up-nginx/#proxy-server) or [Beginner's Guide](https://nginx.org/en/docs/beginners_guide.html) if you are not experienced with Nginx configuration

1. Create the file `index.php`. It will be your API's entry point;
2. Adapt the following configuration file (`example.conf`) to fit your needs:
```nginx
server {
    listen 80;
    server_name example.com;
    index index.php;
    error_log /path/to/example/example.error.log;
    access_log /path/to/example/example.access.log;
    root /path/to/example;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_pass 127.0.0.1:9123;
    }
}
```
3. Reload the server:
```bash
sudo nginx -s reload
```