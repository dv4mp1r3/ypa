**How it works**

Available [here](https://github.com/dv4mp1r3/ypa/blob/master/assets/md/readme.md) on russian


**Setup environment (Ubuntu 16.04)**
```
apt update && apt install php-common apache2 mysql-server rabbitmq-server libapache2-mod-php php-bcmath php-mbstring php-mcrypt php-gd git curl
rabbitmqctl add_user phpconsole consolephp
rabbitmqctl add_user admin admin
rabbitmqctl set_user_tags admin administrator
rabbitmqctl add_vhost /ypa
rabbitmqctl set_permissions -p /ypa phpconsole ".*" ".*" ".*"
rabbitmqctl set_permissions -p /ypa admin ".*" ".*" ".*"
rabbitmq-plugins set rabbitmq_management
echo "[{rabbitmq_management,[{listener, [{port, 15672},{ip, \"::\"}]}]}]." > /etc/rabbitmq/rabbitmq.conf
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

**Setup todo:**
1. Update web-server config
2. Repository clone from git
3. Update php requirements
4. Apply database migrations
5. Write environment test

**Example of rabbitmq message body**
```
"{"taskId":4,"domain":"test.domain","command":"host","extra":null}"
```

**Worker basic command**
```
sudo -u www-data php /var/www/yii rabbit/task
```