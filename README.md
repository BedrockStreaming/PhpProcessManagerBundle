PHP Process Manager Bundle for Symfony Applications
===================================================

**Bring High Performance Into Your Symfony App !**

To kill the expensive bootstrap of PHP and Symfony, this bundle add a simple symfony command to start a process manager using ReactPHP.

This PHP process manager is Symfony specific and designed to be used with an process control system like [supervisord](http://supervisord.org/) - more reliable and avoid reinventing the wheel.

Inspired from [php-pm](https://github.com/php-pm/php-pm).

/!\ Work In Progress !

@todo : use Unix Domain Socket

## Command

```bash
php app/console m6web:http-process [listening-port]
```

Availables options :

- `--memory-max` - Gracefully stop running command when given memory volume, in megabytes, is reached
- `--check-interval` - Interval used to periodically check if we should stop the daemon

## Quick start

Install the bundle :

```bash
composer require m6web/php-process-manager-bundle
```

Start the command :

```bash
php app/console m6web:http-process 8000
```

And open http://localhost:8000 !

## Advanced setup (load balancing)

### Composer

```bash
composer require m6web/php-process-manager-bundle
```

### [Supervisord](http://supervisord.org/)

Example for 8 workers, listening from 8000 to 8007

```ini
[program:mysfproject]
command=php -d memory_limit=1024M app/console m6web:http-process 80%(process_num)02d --env=dev --memory-max=768 --check-interval=60 ; the program (relative uses PATH, can take args)
process_name=%(program_name)s-%(process_num)d ; process_name expr (default %(program_name)s)
numprocs=8                     ; number of processes copies to start (def 1)
directory=/path/to/symfony/    ; directory to cwd to before exec (def no cwd)
umask=022                      ; umask for process (default None)
user=www-data                  ; setuid to this UNIX account to run the program
stdout_logfile=/var/log/supervisord/%(program_name)s-%(process_num)d.log              ; stdout log path, NONE for none; default AUTO
stderr_logfile=/var/log/supervisord/%(program_name)s-%(process_num)d-error.log        ; stderr log path, NONE for none; default AUTO
```

### [NGINX](https://www.nginx.com/resources/wiki/)

Example config for NGiNX and 8 workers:

```nginx
upstream backend  {
    server 127.0.0.1:8000;
    server 127.0.0.1:8001;
    server 127.0.0.1:8002;
    server 127.0.0.1:8003;
    server 127.0.0.1:8004;
    server 127.0.0.1:8005;
    server 127.0.0.1:8006;
    server 127.0.0.1:8007;
}

server {
    root /path/to/symfony/web/;
    server_name servername.com;
    location / {
        try_files $uri @backend;
    }
    location @backend {
        proxy_pass http://backend;
    }
}
```

