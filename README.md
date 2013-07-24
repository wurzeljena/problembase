PROBLEM BASE
============

Implementation of a database for mathematical problems, developed for [Wurzel e.V.]
(http://www.wurzel.org/)

Philosophy
----------
The idea was to provide a database for 40 years of collected problems, to make them
available to the public, searchable, and trackable for intern purposes. Since the mid-1960s,
authors can submit problems to the Wurzel, which publishes six of them every month. Readers
can propose solutions, which are published later as well.

The idea of a problem base was born out of the following thoughts:

*	Given that there are thousands of problems, some with solutions, available in archives,
	why not make them publicly available - to teachers in universities and high schools, to
	interested students?
*	Every once in a while, problems are submitted which have been published long before. How
	can we find that out easily? It is relatively hard to `grep` the TeX archive when looking
	for a problem. Is there a better way? Sure.
*	Sometimes one wants to find problems of a specific type, say number-theoretic or geometric
	problems. That would be especially attractive for teachers.
*	Not every problem that seems simple is. And the same goes for hard-looking ones.
*	Incoming problems usually get sorted into piles like "rejected", "for later", "nice	but
	too easy" and so on. Is there a better and more transparent way? 
*	Some problems get never published though they are not so bad, actually. Sometimes there's
	just too many coming in.

That's where the problem base enters the game. Simply a SQLite database, easily accessible
through a PHP frontend. A web frontend seemed plausible, as the database can be accessed from
different locations, and - originally - to be OS independent. We feature:

*	Multiple proposers per problem - or none at all. Please provide some remarks then.
*	Solutions connected to problems
*	Tagging: they might tell what area of mathematics the problem belongs to
*	Comments: give some quantified evaluation about difficulty, beauty, and required knowledge
	of the problem. And say a few words about how you liked it.
*	Full-text search: technically, it's not a feature of pb but of SQLite. Anyway, you might
	find it useful!
*	Live TeX preview - thanks to [MathJax](http://www.mathjax.org/)
*	Beautiful design using HTML5, CSS3 and JavaScript.

Installation
------------
The problem base runs on Apache+PHP, supported by a SQLite or PostgreSQL data base.
Get a clone by

	git clone --recursive git://github.com/wurzeljena/problembase.git

You can either clone into the document root or a subdirectory. In this case,
add an environment variable `PBROOT=/path/to/problembase`. Also, add

	ErrorDocument 403 /path/to/problembase/error403.php
	ErrorDocument 404 /path/to/problembase/error404.php

to your `httpd.conf`. You might also want to "compile" the `.htaccess` files
into your `httpd.conf`. (e.g, by using [htaccessConverter]
(https://github.com/preinheimer/htaccessConverter))
This speeds up your server a bit. Don't forget to adapt the `RewriteRule`s to
your needs.

### SQLite

Since we are likely dealing with a small number of users, a SQLite database is sufficient. It
also makes backups easy. Create the database as follows:

	cd sql
	sqlite3 -init sqlite.sql problembase.sqlite

In the opening prompt, create a root user:

```sql
INSERT INTO users (name, email, root, editor) VALUES ("Your name", "your@email.com", 1, 1);
```

And we're done: open `http://localhost/path/to/problembase` (which might just be `http://localhost/`)
and enjoy! Don't forget to set a password for your first user.

### PostgreSQL

If you expect more users or want to use a separate database server for some other reason,
[PostgreSQL](http://www.postgresql.org/) is also supported. Create the tables etc. by
executing `sql/postgres.sql`. Since we frequently use `group_concat`, you have to set that
up using this [code](https://gist.github.com/aaronpuchert/6049219). Create an initial user
as above.

To set up the connection, simply set environment variables `DB_HOST`, `DB_NAME`, `DB_USER`,
`DB_PASSWORD` to the appropriate values.

Usage
-----
Although the UI should be straightforward and intuitive, some remarks:

*	we rely on JavaScript. Don't deactivate it.
*	users can do (almost) anything: edit and delete problems, solutions, write comments.
	So be careful! But only a `root` user can add users and grant (or revoke) rights.
*	by using CSS media queries, the layout works on every device. That is, if the device
	happens to support media queries.

Find out how it works
---------------------
Yeah, whatever. I could as well explain it, maybe later.

1.	Play with it,
2.	take a look at [sql/sqlite.sql](https://github.com/wurzeljena/problembase/blob/master/sql/sqlite.sql).
3.	Read the source. It's not that much.

Compatibility
-------------
Though no terrible things should happen, it is recommended to use a recent browser. I do not
test again IE 6 or the like. Since HTML5 provides graceful fallback, the pages should not be
interpreted erronously in older browsers, but they might not display as beautiful.

As of now, full HTML5 compliance is not guaranteed. It's definitely on the list, though.

Security
--------
Some effort has been made to ensure that unauthenticated users can't do any harm to the database.
However, as authenticated user, you can do a lot. So please backup every once in a while, dear
admin. You won't regret. However, thorough security checks still have to be done. Feel free to
analyse the code, I'll be happy about critical feedback.

Passwords are stored as salted SHA-512 hash. To prevent password guessing, one has to wait for 10
seconds after a failed attempt.

What's still missing
--------------------
Take a look at the GitHub [issues](https://github.com/wurzeljena/problembase/issues) page. If
you think you can solve one, do it. Then send a pull request. If you experience some other
issues, please add them or email me. The list is by far not comprehensive, sometimes I don't
write problems down when I think they can be solved quickly.
