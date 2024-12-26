# Https
<IfModule mod_rewrite.c>
    RewriteEngine On

    # HTTP to HTTPS
    RewriteCond %{HTTPS} off [OR]
    RewriteCond %{HTTP:X-Forwarded-Proto} !https
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # www to non-www
    RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
    RewriteRule ^ https://%1%{REQUEST_URI} [L,R=301]
</IfModule>
# Https END
