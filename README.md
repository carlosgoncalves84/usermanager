# usermanager
Symfony 4 - UserManager - Console - App with CRUD functionality

Installation Steps:

1.Clone Repo

2.Run composer install

3.Update DATABASE_URL in .env file with your credentials: mysql://your_db_name:your_db_password@127.0.0.1:3306/usermanagerdb

4.Create database with commmand: php bin/console doctrine:database:create

5.Create DB Table with command: php bin/console doctrine:schema:create  

6.Run UserManagerApp with command: bin/console app:manage-user


Run tests with command: ./vendor/bin/phpunit --no-coverage tests/Service/UserManagerServiceTest.php
