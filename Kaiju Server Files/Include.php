<?php

const DiscordServerName = 'My Community Server!'; # Just put the name of your project or smth to identify it

/*
 * Here put a random string, also you must place it in the
 * configuration of the discord bot, this will simply be a
 * security to verify that the requests come from the BOT,
 * you can find hundreds of websites in google where you can
 * generate a random string.
 */
const APISecretKey = 'SecretPassword';

# Discord Application Settings (from developers portal)
# https://discord.com/developers/applications
const Client_Id = '868209535960088616';
const Secret_Id = '8dQrE88i9wujZxTvch6DC8q27tbVmz6t';

/*
 * If you want the users who have logged in through
 * this page and their accessToken is stored, to be
 * joined to your new server, you must initialize the bot
 * that appears in my github: () and it
 * has to be inside your server.
 */

const Bot_Token = 'ODY4MjA5NTM1OTYwMDg4NjE2.YPsVIw.ad4dw3S9FOK6rL7NAgWWRM5jd20';


/*
 * Here put the same Url of your web page (the Index.php),
 * if you want to make your own theme and want it to be
 * redirected to another website, you must configure it,
 * you can find the parameters in the ReadMe on my GitHub.
 */

const RedirectUrl = 'https://biitez.dev/kaiju/';


/*
 * After users log in through the website, the generated
 * key will be stored in the database, you must import the
 * .mysql file that is in the repository to your database and fill the credentials.
 */


# Your Database Credentials
const DATABASE_USERNAME = ' Database Username Admin';
const DATABASE_PASSWORD = 'Database Password Admin';

# Your Database Info
const DATABASE_HOST = 'localhost'; // default is localhost
const DATABASE_NAME = 'kaiju'; // The name of the database