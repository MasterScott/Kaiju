## What is Kaiju?

Kaiju is an open source verification bot for Discord servers, based on OAuth and with permission for the server owner, to be able to migrate users to a new server in case the current one is suspended.

## How does it work?

Kaiju uses the OAuth of discord, this serves to guarantee access of the user's account with specific permissions to the bot.

The user must enter a website (which can be modified using the instructions below specified), where by clicking the login button, he will be redirected to the discord.com page with OAuth, after logging in, he will be asked to allow the entry of the bot to its account, asking for the permissions `identify` and `guild.join`, these will be used to obtain the ID of the user's account and an access token that will allow the owner to put the user's account to a new server.

### Features:
- Use prepared statements to the database
- Discord bot included to be able to verify users and migrate to a new server
- Discord BOT Made on .NET Core

### Requirements:
- PHP 7.2+
- MySQL
- NET Core Runtime 3.1+ Downloaded (for compatibility with the Discord Bot)


### Concepts
- [Steps](#steps)
- [Intructions for Hosting the Discord Bot](#intructions-discord-bot)
- [Screenshots](#screenshots)
- [Extra](#extra)
- [Credits](#Credits)


### Steps

1. Create an application in the application portal of your account (https://discord.com/developers/applications)

![Portal Developer](https://share.biitez.dev/i/z9fy6.png)

2. Go to your application, go to the section 'OAuth', copy the `Client Id` and` Client Secret`

![OAuth IDs](https://share.biitez.dev/i/uj4lu.png)

3. Open the files from the server, open `Include.php` and paste them where they correspond.

![Includes](https://share.biitez.dev/i/yu3fg.png)

4. In your application portal, go to 'BOT', copy the Token and paste it in `Include.php`.

![Bot Token](https://share.biitez.dev/i/v8w13.png)

5. Go back to the `OAuth` section in the application portal and paste the link where the server files will be in 'Redirects', for example, if you upload the server files to example.com inside `kaiju` directory, there you put `https://example.com/kaiju/`, you should also be able to do it in Include.php

![OAuth Redirect](https://share.biitez.dev/i/zkz1e.png)

![OAuth Redirect](https://share.biitez.dev/i/aqgfi.png)

6. Inside the Include.php file, you will find a variable called `APISecretKey`, there place a Random string, like a password, this will be used in the requests between bot->server, you must also place it in the Settings.json file for the bot's discord.

![SecretKeyBot](https://share.biitez.dev/i/bt7kh.png)
![SecretKeyPHP](https://share.biitez.dev/i/djzpd.png)

7. Import 'kaiju.sql' that you can find in this repository to your database.

![Database Table](https://share.biitez.dev/i/bae5t.png)

8. 

Now, download the Discord Bot that you can find in `Releases`, open the file 'Settings.json' and put the information of your bot that you can find in your previously created application.
You must go to your discord server, create the rank for the members, copy the ID and paste it in `VerifiedRankId`
You must place the URL of the API of this project there, as I mentioned before, if you placed the server files in `https://example.com/` inside the `kaiju` directory, there you must place` https://example.com/kaiju/api/`
In the `KaijuSecretKeyCommunication` space, put the password placed in Include.php, as mentioned in instruction 6.

![Discord Bot Settings](https://share.biitez.dev/i/dqnck.png)

9. Go to the 'OAuth' section of the application on Discord, and put the bot on your server. Also give it administrator permissions.

![Bot OAuth](https://share.biitez.dev/i/fb9a7.png)

![Add Bot](https://share.biitez.dev/i/p0sj9.png)

### Make sure that the rank of the bot is ABOVE the rank of the members.

10. Fill the Include.php file with your database credentials among other things

11. To migrate the users registered in the database to a new server, you must integrate the bot to the new server, copy the ID by right-clicking on the icon and place the command: `!Migrate ID`, if everything goes well, the bot will respond with a command telling you about the users that were entered to the server.

(If users log into the page, there will appear the statistics of when they are migrated.)

![Preview](https://share.biitez.dev/i/3spx7.png)

## Intructions Discord Bot

Extra: Any error will appear in the console.

- [Windows](#bot-windows)
- [Ubuntu](#bot-ubuntu)

## Windows

To host the bot on Windows, make sure you have downloaded `.NET Core Runtime 3.1+` (https://dotnet.microsoft.com/download/dotnet/3.1/runtime), After downloading it, just open the file `Kaiju.DiscordBot.exe`and your bot will start.


## Ubuntu

To host it in Ubuntu, you will need to put commands in the CLI,

First, make sure you have the .NET Core Runtime downloaded by placing the command:

```
dotnet
```

If you receive an error, continue you must type the following commands to download it:


```
sudo apt update 
sudo apt install apt-transport-https -y
sudo apt install dotnet-runtime-3.1 
```

Now, upload the bot files (including the dll's), go to the path and you can open a process with:

```
nohup dotnet Kaiju.DiscordBot.dll > dotnetcore.log &
```

To close it, you must find the `PID` of the process by placing the command:

```
ps wx
```

A list of processes will open, look for the discord bot and type:

```
kill PID
```

### Screenshots:
![Log In](https://share.biitez.dev/i/621x4.png)
![AppLogIn](https://share.biitez.dev/i/bkute.png)


### Extra
This project was created only by me in my free time, if you find any errors, please notify me, if you want to improve it open a pull request!

### Credits:

- biitez#8568 | https://t.me/biitez | BTC Addy : bc1qts3ha9ea0s349tnn05j3c5suk8m42p7a2k38ra
