# Casilium


**NOTE:** Casilium is currently under development.

**Casilium** is a work in-progress open-source ticket system.

## Requirements

  * HTTP server running (apache/nginx)
  * PHP 8.2/8.3 with following extensions:
    * apcu
    * dom
    * iconv
    * pdo
    * pdo_mysql
    * session
    * simplexml
    * sodium
    * tokenizer
    * xml
    * xmlwriter
  * Mysql Server 5.7/8
  
## Installation

The easy way to install Casilium is to clone the git repository

    git clone https://github.com/Casilium/casilium.git

Install via composer:

    cd casilium
    composer install
    
## Configuration


### Create the database in MySQL:

    CREATE USER casilium@localhost IDENTIFIED BY 'password';
    CREATE DATABASE casilium;
    GRANT ALL PRIVILEGES ON *.* TO casilium@localhost;

### Update Configuration files:

**config/autoload/local.php**

Copy the file `config/autoload/local.php.dist` to `config/autoload/local.php` 
and update the credentials to those you created the database with.

For example, if we used the credentials specified above:

    'url' => 'mysql://USERNAME:PASSWORD@localhost/DATABASE_NAME'

Would be changed to:

    'url' => 'mysql://casilium:password@localhost/casilium',

**config/autoload/auth.local.php**

Copy the file `config/autoload/auth.local.php.dist` to `config/autoload/auth.local.php`
and update the file with the correct database credentials.

Example:

    'authentication' => [
        'pdo' => [
            'dsn'   => 'mysql:host=localhost; dbname=YOUR_DBNAME',
            'table' => 'user',
            'field' => [
                'identity' => 'email',
                'password' => 'password',
            ],
        'sql_get_details' => 'SELECT id,status,mfa_enabled FROM user WHERE user.email = :identity',
        'username' => 'YOUR_PASSWORD',
        'password' => 'YOUR_USERNAME',
    ];

Change to:

    'authentication' => [
        'pdo' => [
        'dsn'   => 'mysql:host=localhost; dbname=casilium',
        'table' => 'user',
        'field' => [
            'identity' => 'email',
            'password' => 'password',
        ],
        'sql_get_details' => 'SELECT id,status,mfa_enabled FROM user WHERE user.email = :identity',
        'username' => 'casilium',
        'password' => 'password',
    ];

### Database Migrations
    
First test the database connectivity by issuing the following command from the project
directory:

    ./vendor/bin/doctrine-migrations status

All being well, if you have no issues at this stage you should be shown the migrations status page.
Now execute the migrations to create the database:

    ./vendor/bin/doctrine-migrations migrate


You will need to ensure that the scripts are executable, if not run
    
    chmod a+rx ./vendor/bin/*

From the Casilium project directory

## Web Server Configuration

You'll need to update the configuration of your web server to serve the files from the
`public` folder within the project director, for example:

**Example Apache Configuration**

Files should be served from the "public" directory:

    <VirtualHost *:80>
        ServerName casilium.yourdomain.com
        DocumentRoot /usr/local/www/casilium/public
    </VirtualHost>     

### php.ini

Enable apc within your `php.ini` folder

    apc.enable_cli = 1

## Disable Development Mode

Casilium is current set to development mode,
be sure to disabled development mode.

**Do not run development mode on a production system!**

From within the project directory:

    composer development-disable

License
-------
Casilium is released under the Apache 2.0 license. See the included LICENSE.txt
file for details of the General Public License.

Casilium is uses several open source projects, including
[Laminas](https://getlaminas.org/),
[Mezzio](https://docs.mezzio.dev/),
[Doctrine](https://www.doctrine-project.org/),
[Bootstrap](https://getbootstrap.com/),
[Font-Awesome](https://fontawesome.com/),
[jQuery](https://jquery.com/).

