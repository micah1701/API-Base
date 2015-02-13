# API-Base
An extendable PHP Class for quickly creating new API interfaces

I find that any time I need to create a new API I'm re-writting the same code over and over again, so I made this quick set of classes as a foundation for future projects.
This particular API setup uses signed Hashed Message Authentication Code (HMAC) sent with each REST request to authenticate messages being passed back and forth.

Each authorized user is given a secure identifier (SID) and a secret key.  With each request the user sends the SID and a signature which hashes the message payload with the secret api key.  The secret key is never sent as plain text. On the receiving end, the message is hashed once again and compared with the signature for verification.

There is also a table column that allows for a comma seperated list of allowed IP address that are allowed to access the API.

To manage users, first set up the following database table:

```
CREATE TABLE IF NOT EXISTS `api_accounts` (
  `id` int(11) NOT NULL,
  `sid` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `api_key` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `valid_ips` text COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

Edit the *config.php* file with connection string credentials to your database and update the hash salt and encrypt key, which is used to encrpy the secret key within the database.  Having the encrpyt() and decrypt() functionality included is a handy feature to secure other data that might be stored via the app being built.

The app includes the *idiorm* class (https://github.com/j4mie/idiorm) for easy ORM access to the database.  If you're using something else, just comment this include out in the config file and update the class accordingly in the one place that does a database lookup.

I forgot to add an example way to generate users programmatically, you can build whatever you'd like.  For the sake of getting things up and running, you could do something like this:

```
<?php
include 'config.php';
$newUser = ORM::for_table('api_accounts')->create();
$newUser->sid = md5(microtime(). rand(1000,999999));
$newUser->api_key = encrypt(md5(rand(1000,999999) . microtime() . hash_salt ));
$newUser->active = 1;
$newUser->save();

echo "SID: ". $new->sid ."<br>\n";
echo "Secret Key: ". decrypt($new->api_key);
```
