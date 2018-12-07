### DB Configuration

1. Log into mysql using `mysql -u root -p`
2. Creat a new database with the name `project`.
3. Use the files in `tables_sql` directory to set up the database.
4. `cp api/dbconfig.sample.php api/dbconfig.php`
2. `nano api/dbconfig.php`
3. Modify the `DB_PASS` constant and any other constants as needed
4. Save and exit

### Hosting
Please host on XAMPP. Follow any other configuration instructions for frontend setup.
