Casilium
========

**NOTE:** Casilium is currently under development and **SHOULD NOT**
be used as it is currently in development, not even at Alpha stage.

**Casilium** is a work in-progress open-source ticket system.

Requirements
------------
  * HTTP server running Apache / Nginx / IIS
  * PHP Version 7.4:
    * curl
    * gd
    * iconv
    * intl
    * json
    * mbsring
    * mysqli
    * pdo
    * pdo_mysql
    * pecl-APCu
    * pecl-mcrypt
    * session
    * simplexml
    * sodium
    * xml
  * Mysql Server v5.7
  
Installation
------------
The easy way to install Casilium is to clone the git repository

    git clone https://github.com/sheridans/casilium.git

Install via composer:

    composer install
    
Configuration
-------------

Currently, there is gui installer until the project is more complete.

* Create a suitable database in MySQL
* Copy **config/auth.local.php.dist** to **config/auth.local.php**
  * Modify the **dsn** to suit your needs
  * Modify the username/password fields
* Copy **config/local.php.dist** to **config/local.php**
  * Edit the **url** key for your database details
    
**Database Setup**

Casilium uses Doctrine for database and migrations which enables easy
modification and setup of the databases. To run the database migration
tool, from the project root run:

    ./vendor/bin/doctrine-migrations: migrations:migrate

You will need to ensure that the scripts are executable, if not run

    chmod a+x /vendor/bin/*

**Example Apache Configuration**

Files should be served from the "public" directory:

    <VirtualHost *:80>
        ServerName casilium.yourdomain.com
        DocumentRoot /usr/local/www/casilium/public
    </VirtualHost>     

php.ini
-------

    apc.enable_cli = 1

Disable Development Mode
------------------------
Casilium is current set to development mode,
be sure to disabled development mode.

**Do not run development mode on a production system!**

    composer development-disable

License
-------
Calisium is released under the GPL2 license. See the included LICENSE.txt
file for details of the General Public License.

Casilium is uses several open source projects, including
[Laminas](https://getlaminas.org/),
[Mezzio](https://docs.mezzio.dev/),
[Doctrine](https://www.doctrine-project.org/),
[Bootstrap](https://getbootstrap.com/),
[Font-Awesome](https://fontawesome.com/),
[jQuery](https://jquery.com/).

