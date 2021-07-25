using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Discord;
using Discord.WebSocket;
using Discord.Commands;
using Microsoft.Extensions.DependencyInjection;
using System.Reflection;
using System.IO;
using Newtonsoft.Json;
using Kaiju.DiscordBot.Models;
using Kaiju.DiscordBot.Helpers;

namespace Kaiju.DiscordBot
{
    class Program : KaijuUserHelpers
    {
        static void Main() 
            => new Program().RunBotAsync().GetAwaiter().GetResult();        

        private DiscordSocketClient _client;
        private CommandService _commands;
        private IServiceProvider _services;

        public async Task RunBotAsync()
        {
            _client = new DiscordSocketClient();

            _commands = new CommandService();

            _services = new ServiceCollection()
                .AddSingleton(_client)
                .AddSingleton(_commands)
                .BuildServiceProvider();

            _client.Log += async log =>
            {
                Console.WriteLine(log);
                await Task.CompletedTask;
            };

            if (!File.Exists("settings.json"))
            {
                var settings = new DiscordSettings
                {
                    BotToken = "Your Discord Bot Token",
                    Prefix = "!"
                };

                File.WriteAllText("settings.json", JsonConvert.SerializeObject(settings, Formatting.Indented));

                Console.WriteLine("The default bot configuration has been created, please put your bot token in 'settings.json'");

                await Task.Delay(-1);
            }

            var Settings = JsonConvert.DeserializeObject<DiscordSettings>(File.ReadAllText("settings.json"));

            Globals.KaijuApiWebUrl = Settings.KaijuApiWebUrl ?? throw new ArgumentNullException("Invalid Kaiju API Web Url");
            Globals.VerifiedRank = Settings.VerifiedRankId;
            Globals.SecretKeyCommunication = Settings.KaijuSecretKeyCommunication ?? throw new ArgumentNullException("Invalid Secret Key : To configure it, you only have to place a random key, of any size, you must also include it in the PHP file 'Include.php', this will simply be for the server to validate that the requests come from the BOT.");

            Globals.BotPrefix = Settings.Prefix;

            await _commands.AddModulesAsync(Assembly.GetEntryAssembly(), _services);

            _client.MessageReceived += HandleCommandAsync;

            _client.UserJoined += EntryValidationAsync; // It will check if the user is already verified
            _client.UserBanned += RemoveAuthenticatedUserAsync; // If someone is banned, they will be removed from the authentication

            await _client.LoginAsync(TokenType.Bot, Settings.BotToken);

            await _client.StartAsync();

            await Task.Delay(-1);
        }

        private async Task HandleCommandAsync(SocketMessage arg)
        {
            if (arg is SocketUserMessage message)
            {
                var context = new SocketCommandContext(_client, message);

                int argPos = 0;

                if (message.HasStringPrefix(Globals.BotPrefix, ref argPos))
                {
                    /*var CommandExecResponse = */await _commands.ExecuteAsync(context, argPos, _services);

                    //if (CommandExecResponse.IsSuccess is false)
                    //{
                    //    Console.WriteLine(CommandExecResponse.ErrorReason);
                    //}
                }
            }                 
        }
    }
}
