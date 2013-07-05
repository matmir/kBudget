kBudget
=======

Simple online finance manager

URL: http://www.k-budget.net

Demo application: http://demo.k-budget.net
Demo user:
	login: demo
	password: demodemo

INSTALATION
===========

1) git clone https://github.com/matmir/kBudget.git
2) Change chmod-s to 777 on folders:
	import/
	images/captcha
3) create new MySQL database and import into the new database file data/kbudget.sql
4) create in config/autoload directory configuration files:
	copy db.global.php as db.local.php and in copied file update database name/login/password
	copy email.global.php as email.local.php and in copied file update email fields
5) enter into the site and login:
	login: admin
	password: password
	and change admin password to your own.
