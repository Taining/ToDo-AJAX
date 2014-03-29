Secure Password
	HASH FUNCTION USED: append salt at end of password, hash it using sha256
	SALT USED: generate a unique id with random number; compute its md5 hash value; get first 8 chars of hashed string as salt

Installation Guide
	1. create neccessary tables in your database by running schema.sql
	2. update database settings in config.inc
	3. Congratulations! You can now use our ToDo Manager to schedule yout tasks!

