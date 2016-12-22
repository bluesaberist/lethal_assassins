Lethal Assassins
================

## Setup dev environment
**All installation instructions are for Ubuntu or derivatives**

### PHP
You need to install PHP. Install with `sudo apt-get install php-fpm php php-mysql php-xml`. (php-fpm is needed to because php will otherwise install apache server with it. If that's what you want, then go ahead and skip it.)

### Composer
Next you need Composer. Install by running the commands at the top of the [Composer Downloads page](https://getcomposer.org/download/). You can just directly copy and paste those lines to the terminal. Once all lines are run, there will be a new file `composer.phar` in your current directory. You can either move that into the project directory (source control will ignore it), or move it to `/usr/local/bin`, rename to `composer`, and change owner to root. (as a single command: `sudo mv composer.phar /usr/local/bin/composer && sudo chown root:root /usr/local/bin/composer`). If put in the project directory, you'll run it with `./composer.phar`, rather than just `composer`.

### MySQL
Then you need a MySQL server to connect to. You can install MySQL locally, and run through all the setup stuff, OR you can install docker, and keep it in a container, where you can easily deal with it. So, [install docker](https://docs.docker.com/engine/installation/linux/ubuntulinux/).

Create a dockerized mysql container with:
```
docker run --name lethal-mysql -e MYSQL_ROOT_PASSWORD="super-awesome-password" -v /var/data:/var/lib/mysql -p 3306:3306 -d mysql:5.7
```
"super-awesome-password" should be changed to something else.
You can make sure the container is running with `docker ps`.

The instance will persist data by storing it in `/var/data`. You can change this location in the above command (e.g., to store in `/var/mysql/data`, that part will be `-v /var/mysql/data:/var/lib/mysql`).

You can connect to the MySQL instance as user root and password as above (super-awesome-password). You should run the SQL in `database-schema.sql`, then create a new user for the app who has read/write access to schema `lethal_assassins`. If you have test data, now would be a good time to insert it as well.

You should spin it down with `docker stop lethal-mysql`.

### Symfony install
Now that we have the necessary tools, we can install the project dependencies. From the project root, run `composer install` (or if composer wasn't moved to `/usr/local/bin`: `./composer.phar install`). This may take a minute, and likely will show a few warnings. Eventually it will ask you to check or fill a couple parameters.

The `database_host` and `database_port` defaults will probably work fine. For `database_name`, the default -- `lethal_assassins` -- will be correct if you used the above `database-schema.sql` file as-is. `database_user` and `database_password` will be what you set for the new user you created in MySQL.

After inputing the parameters above, Composer will clear the cache and the app is ready to run.

### Run dev environment!
`package.json` is already setup to run the environment from there. Just run `npm start` to spin up the docker container and start the built-in PHP dev server. Then when you are finished, run `npm run end` to put both services to rest. The MySQL container will persist on your system, and the above line will have it store data in `/var/data`.

After running `npm start`, you can visit `localhost:8000` to see the app.

## Production server
On a production server (not using the built-in PHP server), the cache clear will likely fail due to a user problem. To clear the cache when the server user is not you, use `sudo -u <web server user> php bin/console c:cl`, where "<web server user>" is probably `www-data`, `apache`, or `nginx`. package.json has a shortcut for clearing cache with www-data which can be run with `npm run cache-clear`.

Assuming that the server can pull from origin/master and clear the cache as user `www-data`, then `npm run deploy` can be used to do the deployment on the server.
