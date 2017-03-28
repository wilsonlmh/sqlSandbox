# sqlSandbox
Secure way to let user execute custom SQL

#How it works:

- Create a limited user who only able to read certian DB
- Run SQL like 'CREATE TEMPORARY TABLE IF NOT EXISTS table2 AS (SELECT * FROM table1);' to create temporary table(s)
- Use sqlSandbox::config["tablesWhiteList"] to limit user can only access templorary table(s) created above
- run sqlSandbox::parseAndCheck() to filter out statements
- execute output of parseAndCheck()

#Dependency

 - PHP-SQL-Parser (https://github.com/greenlion/PHP-SQL-Parser)
 
 #License
 
LGPLv3
