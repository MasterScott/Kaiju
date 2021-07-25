using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using Discord;
using Discord.Commands;
using Discord.WebSocket;
using Kaiju.DiscordBot.Helpers;
using Newtonsoft.Json;

namespace Kaiju.DiscordBot
{
    public class Commands : ModuleBase<SocketCommandContext>
    {

        [Command("verify")]
        public async Task VerifyUser(string VerificationCode = null)
        {
            await Context.Message.AddReactionAsync(new Emoji("👌"));

            var guildUser = (SocketGuildUser)Context.User;           

            if (guildUser.Roles.Any(x => x.Id == Globals.VerifiedRank))
            {
                await ReplyAsync("You are already verified!");
                return;
            }

            if (string.IsNullOrEmpty(VerificationCode))
            {
                await ReplyAsync("You must write the verification code!");
                return;
            }

            var VerificationUserResponse = await KaijuRequestHelper.SendRequestAsync(Enums.KaijuAPIMethods.VERIFY_USER, guildUser.Id, VerificationCode);

            if (VerificationUserResponse == null) 
            {
                await ReplyAsync("An error occurred, for more information check the Console. - User Id: " + guildUser.Id);
                return;
            }

            if (VerificationUserResponse.StatusServer == "USER_VERIFIED")
            {
                try
                {
                    await guildUser.AddRoleAsync(Globals.VerifiedRank);

                    try
                    {
                        await guildUser.SendMessageAsync("You were successfully verified!");
                    }
                    catch
                    {
                        // ignored
                    }
                }
                catch
                {
                    try
                    {
                        await guildUser.SendMessageAsync($"Hey {guildUser.Mention}, I don't have permissions to give the role with the Id '{Globals.VerifiedRank}', please tell to staff to position me above it on the rank list and make sure I have the necessary permissions.");
                    }
                    catch
                    {
                        await ReplyAsync($"Hey {guildUser.Mention}, I don't have permissions to give the role with the Id '{Globals.VerifiedRank}', please tell to staff to position me above it on the rank list and make sure I have the necessary permissions.");
                    }
                }
            }
            else if (VerificationUserResponse.StatusServer == "NOT_BINDED")
            {
                try
                {
                    await guildUser.SendMessageAsync($"Hey {guildUser.Mention}, The verification token ({VerificationCode}) does not belong to the account that was verified on the website, please do so from your current account.");
                }
                catch
                {
                    await ReplyAsync($"Hey {guildUser.Mention}, The verification token does not belong to the account that was verified on the website, please do so from your current account.");
                }
            }
            else if (VerificationUserResponse.StatusServer == "UNFOUND_TOKEN")
            {
                try
                {
                    await guildUser.SendMessageAsync($"Hey, The verification code ({VerificationCode}) was not found.");
                }
                catch
                {
                    await ReplyAsync($"Hey {guildUser.Mention}, The verification code was not found.");
                }                
            }

            await Context.Message.DeleteAsync();

            return;
        }

        [Command("migrate")]
        [RequireUserPermission(GuildPermission.Administrator, ErrorMessage = "Administrator required.")]
        public async Task MigrateUsers(string _ServerId = null)
        {
            await Context.Message.AddReactionAsync(new Emoji("👌"));

            var guildUser = (SocketGuildUser)Context.User;

            var VerificationUserResponse = await KaijuRequestHelper.SendRequestAsync(Enums.KaijuAPIMethods.MIGRATE_USERS, guildUser.Id, ServerId: _ServerId);

            if (VerificationUserResponse == null)
            {
                await ReplyAsync("An error occurred, for more information check the Console. - Server Id: " + _ServerId);
                return;
            }

            if (VerificationUserResponse.StatusServer == "NO_USERS")
            {
                await ReplyAsync("You don't have users on the database.");
            }
            else if (VerificationUserResponse.StatusServer == "BAD_TOKEN_DISCORD_BOT_INCLUDED")
            {
                await ReplyAsync("Bad Discord Bot Token, please verify it on the PHP File 'Include.php'");
            }
            else if (VerificationUserResponse.StatusServer.Contains("|")) // I know it's not the best practice
            {
                var MigratedCounter = VerificationUserResponse.StatusServer.Split('|');
                await ReplyAsync($"{MigratedCounter[1]} Users were successfully migrated, with {MigratedCounter[2]} users getting an error status code from discord (maybe suspenders), {MigratedCounter[0]} users in total.");
            }

            await Context.Message.DeleteAsync();

            return;
        }
    }
}
