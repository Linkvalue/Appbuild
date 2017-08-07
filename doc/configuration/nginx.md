# Configuration - NGINX

In this configuration sample, we will assume that :

- the domain name pointing to your HTTP server is: `domain.tld`
- the application sources location is: `/var/www/MajoraOTAStore`
- you left the default value of `builds_application_dir` in app/config/parameters.yml which is: `'%kernel.root_dir%/../var/build_files'`

## Full NGINX configuration sample

```nginx
server {
    server_name domain.tld;
    root /var/www/MajoraOTAStore/web;

    # HTTPS is required to download iOS applications
    listen 443 ssl;
    ssl on;
    ssl_certificate /path/to/fullchain.pem;
    ssl_certificate_key /path/to/privkey.pem;

    # Upload max size (don't forget to also set values of "post_max_size" and "upload_max_filesize" in php.ini)
    client_max_body_size 500M;

    # OPTIONAL BUT RECOMMENDED - Serve protected build files directly from nginx instead of PHP
    # This feature is called "X-Sendfile" -> see https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile
    # (part 1 on 2)
    location /protected/build_files {
        internal;
        alias /var/www/MajoraOTAStore/var/build_files;
    }

    # Try to serve file directly, fallback to app.php which is Symfony front controller
    location / {
        try_files $uri /app.php$is_args$args;
    }

    # send "app.php" requests to "php-fpm"
    location ~ ^/app\.php(/|$) {
        # FastCGI configuration from nginx documentation => see https://www.nginx.com/resources/wiki/start/topics/examples/phpfcgi
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        if (!-f $document_root$fastcgi_script_name) {
            return 404;
        }
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_param HTTP_PROXY "";

        # Force HTTPS
        fastcgi_param HTTPS on;

        # X-Sendfile feature (part 2 on 2)
        fastcgi_param HTTP_X_SENDFILE_TYPE X-Accel-Redirect;
        fastcgi_param HTTP_X_ACCEL_MAPPING /var/www/MajoraOTAStore/var/build_files/=/protected/build_files/;

        # Prevent URIs from containing "/app.php"
        internal;
    }

    # Return 404 for all other php files not matching the front controller this prevents access to other php files you don't want to be accessible
    location ~ \.php$ {
        return 404;
    }
}
```
