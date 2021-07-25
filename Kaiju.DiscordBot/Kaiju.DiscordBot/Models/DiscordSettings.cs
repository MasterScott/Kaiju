using System;
using System.Collections.Generic;
using System.Text;

namespace Kaiju.DiscordBot.Models
{
    public class DiscordSettings
    {
        public string KaijuApiWebUrl { get; set; }
        public string KaijuSecretKeyCommunication { get; set; }
        public ulong VerifiedRankId { get; set; }
        public string BotToken { get; set; }
        public string Prefix { get; set; }
    }
}
