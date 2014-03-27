DROP TABLE appuser CASCADE;
DROP TABLE tasks CASCADE;

CREATE TABLE appuser (
	uid SERIAL,
	email VARCHAR(100),
	fname VARCHAR(20),
	lname VARCHAR(20),
	sex INTEGER, -- 1 is female, 2 is male
	password VARCHAR(100),
	birthday DATE,
	news boolean,
	signupdate DATE,
	done INTEGER, 	-- Number of task units that have been completed
	PRIMARY KEY (uid)
);

CREATE TABLE tasks(
	uid INTEGER,
	taskid INTEGER PRIMARY KEY,
	dscrp VARCHAR(40),
	details VARCHAR(1000),
	total INTEGER,
	progress INTEGER,
	ordering INTEGER, -- Order of displaying
	priority INTEGER,
	createtime DATE,
	--finishtime DATE, 
	FOREIGN KEY (uid) REFERENCES appuser(uid)
);
	
-- Adding a sample row to appuser TABLE
-- Password is 12345
INSERT INTO appuser (email, fname, lname, sex, password, birthday, news, signupdate, done) VALUES ('test@localhost', 'test', 'todo', '2', '827ccb0eea8a706c4c34a16891f84e7b', '2014-02-12', true, '2014-02-12', 0);