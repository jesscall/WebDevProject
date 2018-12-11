### DB Set Up

1. Log into mysql using `mysql -u root -p` (Or a different username)
2. Create a new database: `CREATE DATABASE project`
3. `source sql/structure.sql`

### DB Configuration

4. `cp api/dbconfig.sample.php api/dbconfig.php`
2. `nano api/dbconfig.php`
3. Modify the `DB_PASS` constant and any other constants as needed
4. Save and exit

### Hosting
Please host on XAMPP. Follow any other configuration instructions for frontend setup.

### Note on Apache2

The `api/.htaccess` file uses the `Header` command.
This requires the `headers` module to be enabled.

You may enable it using `a2enmod headers`.
Restart apache2 after this.