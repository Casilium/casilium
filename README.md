Casilium
========

**NOTE:** Casilium is currently under development and **SHOULD NOT**
be used as it is currently incomplete!

**Casilium** is an enterprise level open source ticket system, it is 
an attractive alternative to higher-cost and complex customer support
systems; lightweight, reliable, web based and easy to set up and use; 
and it's completely free.

Requirements
------------
  * HTTP server running Apache / Nginx / IIS
  * PHP Version 7.2 or higher with the following exensions:
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
  * Modify the dsn to suit your needs
  * Modify te username/password fields
* Copy **config/local.php.dist** to **config/local.php**
  * Edit the *url* key for your database details
    
License
-------
Calisium os released under the GPL2 license. See the included LICENSE.txt
file for details of the General Public License.

Calsium is uses several open source projects, including
[Laminas](https://getlaminas.org/),
[Mezzio](https://docs.mezzio.dev/),
[Bootstrap](https://getbootstrap.com/),
[Font-Awesome](https://fontawesome.com/),
[jQuery](https://jquery.com/).

