# Nginx FastCGI server parameters `fastcgi_param` for PHP FPM (FastCGI Process Manager)

---
# Preflight

Settings for `fastcgi_param`s server parameters for PHP FPM (FastCGI Process Manager) on Nginx.

* Adhere to [RFC 3875](https://tools.ietf.org/html/rfc3875) (further - *Standard*).
* Add `fastcgi_param` entries that are not in *Standard*, but are required op and security wise.
* Add parameters that are *commonly used* mostly because PHP code has relied on exposed Apache server internals over years.
* Assume that FastCGI runs on the same machine as Nginx instance.

Written on `Ubuntu Server 18.04.3 LTS`, `nginx/1.17.7`, `PHP 7.4.1`.

---
# The Result

## Files in directory `fastcgi_params-php`

* [`fastcgi_params-php/000-core.conf`](../master/fastcgi_params-php/000-core.conf)
* [`fastcgi_params-php/001-common.conf`](../master/fastcgi_params-php/001-common.conf)

## Usage example

### Using [*evil if*](https://www.nginx.com/resources/wiki/start/topics/depth/ifisevil/)

```nginx
    location ~ [^/]\.php(/|$) {
        fastcgi_index   index.php;
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        if (!-f $document_root$fastcgi_script_name) { return 404; }
        include         /etc/nginx/fastcgi_params-php/000-core.conf;
        include         /etc/nginx/fastcgi_params-php/001-common.conf;
        fastcgi_pass    unix:/run/php/php7.4-fpm_nginx_nginx_nginx.sock;
    }
```

### Using `try_files $fastcgi_script_name`

```nginx
    location ~ [^/]\.php(/|$) {
        fastcgi_index   index.php;
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        set $saved_fastcgi_path_info $fastcgi_path_info; # http://trac.nginx.org/nginx/ticket/321
        try_files $fastcgi_script_name =404;
        include         /etc/nginx/fastcgi_params-php/000-core.conf;
        include         /etc/nginx/fastcgi_params-php/001-common.conf;
        fastcgi_param   PATH_INFO           $saved_fastcgi_path_info;
        fastcgi_param   PATH_TRANSLATED     $document_root$saved_fastcgi_path_info;
        fastcgi_pass    unix:/run/php/php7.4-fpm_nginx_nginx_nginx.sock;
    }
```

⚠️ The issue is that `try_files $fastcgi_script_name` clears `$fastcgi_path_info`. See [ticket #321](http://trac.nginx.org/nginx/ticket/321).

* Redefine `fastcgi_param`s that rely on `$fastcgi_path_info` after conf file include using `$saved_fastcgi_path_info `.  
* It may be impractical to try to track all `fastcgi_param`s that use `$fastcgi_path_info`, thus files at `fastcgi_params-php/*.conf` can use `$saved_fastcgi_path_info ` explicitly.

## Test PHP

[`html/index.php`](../master/html/index.php) holds some helper code to run.

---
# Configuration

Nginx variables reference

* [`ngx_http_core_module` Embedded Variables](http://nginx.org/en/docs/http/ngx_http_core_module.html#variables)
* [`ngx_http_fastcgi_module` Embedded Variables](http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#variables)

## Core required

It will be assumed that ***Standard is required***, although obviously PHP applications can be run without defining all parameters in Standard.

### The Common Gateway Interface (CGI) Version 1.1, RFC 3875

[*Standard*](https://tools.ietf.org/html/rfc3875) defines [seventeen](https://tools.ietf.org/html/rfc3875#section-4.1) *Request Meta-Variables*.

1. [AUTH_TYPE](https://tools.ietf.org/html/rfc3875#section-4.1.1)

     > For HTTP, if the client request required authentication for external access, then the server MUST set the value (..)

     Optional.

     `fastcgi_param   AUTH_TYPE           "";`

     Setting `AUTH_TYPE` to empty. Note that *HTTP Basic Authentication* can be implemented in Nginx, see [`ngx_http_auth_basic_module`](http://nginx.org/en/docs/http/ngx_http_auth_basic_module.html), if so, then according to *Standard* value should be set to `"Basic"`.

2. [CONTENT_LENGTH](https://tools.ietf.org/html/rfc3875#section-4.1.2)

     > The server MUST set this meta-variable if and only if the request is accompanied by a message-body entity.

     Required.

     `fastcgi_param   CONTENT_LENGTH      $content_length;`

3. [CONTENT_TYPE](https://tools.ietf.org/html/rfc3875#section-4.1.3)

     Required.

     `fastcgi_param   CONTENT_TYPE        $content_type;`

4. [GATEWAY_INTERFACE](https://tools.ietf.org/html/rfc3875#section-4.1.4)

     > The GATEWAY_INTERFACE variable MUST be set to the dialect of CGI being used by the server to communicate with the script.

     Required.

     `fastcgi_param   GATEWAY_INTERFACE   CGI/1.1;`

5. [PATH_INFO](https://tools.ietf.org/html/rfc3875#section-4.1.5)

     > The PATH_INFO variable specifies a path to be interpreted by the CGI script.  It identifies the resource or sub-resource to be returned by the CGI script, and is derived from the portion of the URI path hierarchy following the part that identifies the script itself.

     `fastcgi_param   PATH_INFO           $fastcgi_path_info; # ⚠️`

6. [PATH_TRANSLATED](https://tools.ietf.org/html/rfc3875#section-4.1.6)

     > The server SHOULD set this meta-variable if the request URI includes a path-info component.

     Required.

     `fastcgi_param   PATH_TRANSLATED     $document_root$fastcgi_path_info; # ⚠️`

     Set always to avoid logic test against `$fastcgi_path_info`.  
     One `$realpath_root$fastcgi_path_info` if one wants to resolve symbolic links.

7. [QUERY_STRING](https://tools.ietf.org/html/rfc3875#section-4.1.7)

     > The server MUST set this variable; if the Script-URI does not include a query component, the QUERY_STRING MUST be defined as an empty string ("").

     Required.

     `fastcgi_param   QUERY_STRING        $query_string;`

     `$args` is *alias*.

8. [REMOTE_ADDR](https://tools.ietf.org/html/rfc3875#section-4.1.8)

     > The REMOTE_ADDR variable MUST be set to the network address of the client sending the request to the server.

     Required.

     `fastcgi_param   REMOTE_ADDR         $remote_addr;`

9. [REMOTE_HOST](https://tools.ietf.org/html/rfc3875#section-4.1.9)

     > The server SHOULD set this variable. If the hostname is not available for performance reasons or otherwise, the server MAY substitute the REMOTE_ADDR value.

     Optional.

     `fastcgi_param   REMOTE_HOST         $remote_addr;`

10. [REMOTE_IDENT](https://tools.ietf.org/html/rfc3875#section-4.1.10)

     > The REMOTE_IDENT variable MAY be used (..) The server may choose not to support this feature, or not to request the data for efficiency reasons, or not to return available identity data.

     Optional.

     `NOT SET`

11. [REMOTE_USER](https://tools.ietf.org/html/rfc3875#section-4.1.11)

     > If the client request required HTTP Authentication, then the value of the REMOTE_USER meta-variable MUST be set to the user-ID supplied.

     Optional.

     `NOT SET`

     Given that `AUTH_TYPE` is `""` it is (can not) not set.

12. [REQUEST_METHOD](https://tools.ietf.org/html/rfc3875#section-4.1.12)

     > The REQUEST_METHOD meta-variable MUST be set to the method which should be used by the script to process the request.

     Required.

     `fastcgi_param   REQUEST_METHOD      $request_method;`

13. [SCRIPT_NAME](https://tools.ietf.org/html/rfc3875#section-4.1.13)

     > The SCRIPT_NAME variable MUST be set to a URI path (not URL-encoded) which could identify the CGI script.

     Required.

     `fastcgi_param   SCRIPT_NAME         $fastcgi_script_name;`

14. [SERVER_NAME](https://tools.ietf.org/html/rfc3875#section-4.1.14)

     > The SERVER_NAME variable MUST be set to the name of the server host to which the client request is directed.

     Required.

     `fastcgi_param   SERVER_NAME         $server_name;`

15. [SERVER_PORT](https://tools.ietf.org/html/rfc3875#section-4.1.15)

     > The SERVER_PORT variable MUST be set to the TCP/IP port number on which this request is received from the client.

     Required.

     `fastcgi_param   SERVER_PORT         $server_port;`

16. [SERVER_PROTOCOL](https://tools.ietf.org/html/rfc3875#section-4.1.16)

     > The SERVER_PROTOCOL variable MUST be set to the name and version of the application protocol used for this CGI request.

     Required.

     `fastcgi_param   SERVER_PROTOCOL     $server_protocol;`

17. [SERVER_SOFTWARE](https://tools.ietf.org/html/rfc3875#section-4.1.17)

     > The SERVER_SOFTWARE meta-variable MUST be set to the name and version of the information server software making the CGI request*

     Required.

     `fastcgi_param   SERVER_SOFTWARE     nginx/$nginx_version;`

### Required Extra


1. [SCRIPT_FILENAME](https://github.com/php/php-src/blob/master/sapi/fpm/fpm/fpm_main.c#L958)

     > The absolute pathname of the currently executing script.

     Required.

     `fastcgi_param   SCRIPT_FILENAME     $document_root$fastcgi_script_name;`

     This is not in *Standard*, but one cannot live without it in PHP. Check `env_script_filename` and `script_path_translated` usage in PHP source.

     [ref1 - PHP source](https://github.com/php/php-src/blob/master/sapi/fpm/fpm/fpm_main.c#L958)


### Required Security

1. [REDIRECT_STATUS](https://httpd.apache.org/docs/2.4/custom-error.html#variables)

     > cgi.force_redirect is necessary to provide security running PHP as a CGI under most web servers. Left undefined, PHP turns this on by default. You can turn it off here AT YOUR OWN RISK.  
     > The configuration directive cgi.force_redirect prevents anyone from calling PHP directly with a URL like http://my.host/cgi-bin/php/secretdir/script.php. Instead, PHP will only parse in this mode if it has gone through a web server redirect rule.

     Required.

     `fastcgi_param   REDIRECT_STATUS     200;`

     This is needed for to align Nginx config with Apache behaviour as PHP relies on *the non-standard CGI environment variable REDIRECT_STATUS on redirected requests*.  
     As of version 5.3.0 PHP is built with `--enable-force-cgi-redirect` enabled by default and `cgi.force_redirect=1` is default.

     [ref - cgi.force_redirect](https://www.php.net/manual/en/security.cgi-bin.force-redirect.php)

2. [HTTP_PROXY](https://httpoxy.org/#fix-now)

     > httpoxy is a set of vulnerabilities that affect application code running in CGI, or CGI-like environments.

     Required.

     `fastcgi_param   HTTP_PROXY          "";`

     Mitigating the *HTTPoxy Vulnerability*.

     [ref1 - httpoxy.org](https://httpoxy.org)  
     [ref2 - Nginx blog](https://www.nginx.com/blog/mitigating-the-httpoxy-vulnerability-with-nginx)

## Commonly used

The [PHP manual on Predefined Variables](https://www.php.net/manual/en/reserved.variables.server.php) probably sums it up.

> There is no guarantee that every web server will provide any of these; servers may omit some, or provide others not listed here.

It lists thirty eight parameters (excluding `argv` and `argc`).

### Really common

1. [HTTPS](https://httpd.apache.org/docs/2.4/expr.html)

     > Will contain the text "on" if the connection is using SSL/TLS, or "off" otherwise.

     Common.

     `fastcgi_param   HTTPS           $https if_not_empty;`

     Apache server internals. It is commonly used by Apache `mod_rewrite`.  
     Used in frameworks. Introduce this variable also in Nginx.

     [ref1 - WordPress source](https://core.svn.wordpress.org/branches/5.3/wp-load.php)  
     [ref2 - Nextcloud source](https://github.com/nextcloud/server/blob/master/lib/private/AppFramework/Http/Request.php#L709)

2. [DOCUMENT_ROOT](http://httpd.apache.org/docs/current/mod/mod_cgi.html#env)

     > The DocumentRoot of the current vhost.

     Common.

     `fastcgi_param   DOCUMENT_ROOT   $document_root;`

     Apache server internals. It is commonly used by Apache `mod_rewrite` and `mod_cgi` module.  
     Used in frameworks. Introduce this variable also in Nginx.

     [ref1 - Apache expression parser](https://httpd.apache.org/docs/2.4/expr.html#vars)  
     [ref2 - mod_rewrite](http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewritecond)  
     [ref3 - Apache Execution of CGI scripts](http://httpd.apache.org/docs/current/mod/mod_cgi.html#env)  
     [ref4 - WordPress source](https://core.svn.wordpress.org/branches/5.3/)

3. [REQUEST_URI](http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewriteco)

     > The path component of the requested URI, such as "/index.html". This notably excludes the query string which is available as its own variable named QUERY_STRING.

     Common.

     `fastcgi_param   REQUEST_URI     $request_uri;`

     Apache server internals. It is commonly used by Apache `mod_rewrite` module.  
     Used in frameworks. Introduce this variable also in Nginx.

     [ref1 - Apache expression parser](https://httpd.apache.org/docs/2.4/expr.html#vars)  
     [ref2 - mod_rewrite](http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewritecond)  
     [ref3 - Laravel source](https://github.com/laravel/laravel/blob/master/server.php#L11)  
     [ref4 - WordPress source](https://core.svn.wordpress.org/branches/5.3/)  
     [ref5 - Nextcloud source](https://github.com/nextcloud/server/) 

## Variables to skip from supplied Nginx config

Default Nginx install provides `/etc/nginx/fastcgi_params` file. As of writing it

* does not contain some Request Meta-Variables defined in *Standard*
* contains variables that are not in *Standard*, but are more or less commonly used in PHP
* contains variables that are not in *Standard* and probably should not be set at all in context of PHP

[ref1 - Nginx source](https://github.com/nginx/nginx/blob/master/conf/fastcgi_params)

1. [DOCUMENT_URI](https://httpd.apache.org/docs/current/mod/mod_include.html#includevars)

     > Same as REQUEST_URI

     Apache server internals. It is also used for SSI in Apache `mod_include`.  
     Enable if really needed.

     [ref1 - Apache expression parser](https://httpd.apache.org/docs/2.4/expr.html#vars)  
     [ref2 - Apache SSI](https://httpd.apache.org/docs/current/mod/mod_include.html#includevars)  
     [ref3 - WordPress source](https://core.svn.wordpress.org/branches/5.3/)

2. [SERVER_ADDR](http://httpd.apache.org/docs/current/mod/mod_cgi.html#env)

     > The IP address of the Virtual Host serving the request.

     Apache `mod_cgi` internals.  
     Enable if really needed.

     [ref1 - Apache Execution of CGI scripts](http://httpd.apache.org/docs/current/mod/mod_cgi.html#env)

3. [REQUEST_SCHEME](http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewritecond)

     > Will contain the scheme of the request (usually "http" or "https").

     Apache server internals. It is commonly used by Apache `mod_rewrite` module.  
     Enable if really needed.

     [ref1 - Apache expression parser](https://httpd.apache.org/docs/2.4/expr.html#vars)  
     [ref1 - mod_rewrite](http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewritecond)

4. [REMOTE_PORT](https://httpd.apache.org/docs/2.4/expr.html#vars)

     > The port of the remote host (2.4.26 and later)

     Apache server internals.  
      Enable if really needed.

     [ref1 - Apache expression parser](https://httpd.apache.org/docs/2.4/expr.html#vars)




