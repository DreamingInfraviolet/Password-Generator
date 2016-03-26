# rp.amareth.space

This PHP application is designed to increase security by generating
unique passwords for different websites.
These passwords do not change over time and are different for each user,
meaning that if one of your accounts is compromised the others will not.

## Privacy
Only anonymous information is stored in the database for additional features.
It is not possible to extract your passwords, the website you generated passwords for,
or the generated passwords from the database. All information is hashed one-way only.
This means that anyone who hacks the server will not be able to steal your information.
However it does mean that if you forget your two identifying passwords, you will no longer
be able to generate passwords.

## Safety
This code can ideally be run on any machine and will generate the same passwords for
everyone.

To minimise lost passwords, it cleans up user input, does not use installation-specific
information and does not create radically different passwords for different constraints if
the change in constraints does not affect the password (for example, if yesterday you specify
    a constraint that the password must be greater than 2 characters in length and you get a
    10-letter password, if you regenerate it again it should remain the same even if you
    set a minimum character constraint of 9 letters).

However, what I took care to ensure that the system works flawlessly, I can not guarantee
that passwords will not be lost if you do not write them down.

# Installation
To set up this code on your own server, you should just copy these files to a home directory.
You may need to change your database information in dbinfo.php. You must then run the setup.sql
file to create the relevant table in your database.
