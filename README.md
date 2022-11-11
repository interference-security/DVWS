# OWASP Damn Vulnerable Web Sockets (DVWS)
OWASP Damn Vulnerable Web Sockets (DVWS) is a vulnerable web application which works on web sockets for client-server communication. The flow of the application is similar to [DVWA](https://github.com/ethicalhack3r/DVWA). You will find more vulnerabilities than the ones listed in the application.

https://owasp.org/www-project-damn-vulnerable-web-sockets/

## Requirements
In the ```hosts``` file of your attacker machine create an entry for ```dvws.local``` to point at the IP address hosting the DVWS application.

Location of ```hosts``` file:

Windows: ```C:\windows\System32\drivers\etc\hosts```

Linux: ```/etc/hosts```

Sample entry for ```hosts``` file:
```
192.168.100.199         dvws.local
```

The application requires the following:

Apache + PHP + MySQL

PHP with MySQLi support

[Ratchet](https://github.com/ratchetphp/Ratchet)

[ReactPHP-MySQL](https://github.com/bixuehujin/reactphp-mysql/)

Install "Ratchet" and "ReactPHP-MySQL" using composer:
```
git clone https://github.com/interference-security/DVWS
cd DVWS
composer install
```

## Docker Installation
```bash
docker build -t dvws .

# For connecting with existing database
docker run -it \
  --name DVWS \
  -p 8080:8080 -p 8888:8888 \
  -e "DB_HOST=db" \
  -e "DB_USER=dvws" \
  -e "DB_PASSWORD=DVWS" \
  -e "DB_DATABASE=dvws" \
  --restart always \
  dvws

# or use docker-compose
docker-compose up
```

Visit http://localhost:8080/setup.php for getting started

## Setting up DVWS
Set the MySQL hostname, username, password and an existing database name in the ```includes/connect-db.php``` file then go to Setup to finish setting up DVWS.

## Running DVWS
On the host running this application, run the following command from DVWS directory: ```php ws-socket.php --heartbeat-interval <seconds>```

Example: ```php ws-socket.php --heartbeat-interval 10```

## Important Note
DVWS has been developed with limited knowledge of Web Sockets. Feel free to contribute and enhance this project.

## Screenshot
![image](https://user-images.githubusercontent.com/5358495/119394820-a725e580-bca0-11eb-9cc7-d31fc30572ce.png)

