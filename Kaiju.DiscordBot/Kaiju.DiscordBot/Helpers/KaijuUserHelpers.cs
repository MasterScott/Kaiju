using Discord;
using Discord.WebSocket;
using Kaiju.DiscordBot.Enums;
using System;
using System.Linq;
using System.Threading.Tasks;

namespace Kaiju.DiscordBot.Helpers
{
    public class KaijuUserHelpers
    {        
        internal static async Task RemoveAuthenticatedUserAsync(SocketUser User, SocketGuild Guild)
            => await KaijuRequestHelper.SendRequestAsync(KaijuAPIMethods.REMOVE_USER, User.Id);

        internal static async Task EntryValidationAsync(IGuildUser User)
        {
            var EntryValidationResponse = await KaijuRequestHelper.SendRequestAsync(KaijuAPIMethods.VERIFY_ENTRY, User.Id);

            if (EntryValidationResponse.StatusServer == "USER_IN_DATABASE" && !User.RoleIds.Any(x => x == Globals.VerifiedRank))
            {
                try
                {
                    await User.AddRoleAsync(Globals.VerifiedRank);
                }
                catch
                {
                    try
                    {
                        await User.SendMessageAsync($"Hey, I don't have permissions to give the role with the Id '{Globals.VerifiedRank}', please tell to staff to position me above of the verified rank it and make sure I have the necessary permissions.");
                    }
                    catch
                    {
                        // Ignored
                    }

                    Console.WriteLine($"Hey, I don't have permissions to give the verified role with the Id '{Globals.VerifiedRank}', please tell to staff to position me above of the verified rank it and make sure I have the necessary permissions.");
                }
            }
        }
    }
}
