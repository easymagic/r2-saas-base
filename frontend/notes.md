# React/Vite SPA fallback and static asset handling

# 1. Serve static assets directly if they exist
RewriteEngine On
RewriteBase /demo/

# Don’t rewrite requests for existing files or folders
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# 2. SPA Fallback: everything else gets served index.html
RewriteRule ^ index.html [L]