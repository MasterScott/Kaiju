using Kaiju.DiscordBot.Enums;
using Kaiju.DiscordBot.Models;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace Kaiju.DiscordBot.Helpers
{
    public class KaijuRequestHelper
    {
        private static readonly HttpClient httpClient = new HttpClient();

        public static async Task<KaijuAPIResponseModel> SendRequestAsync(KaijuAPIMethods Method, ulong UserId, string VerificationToken = null, string ServerId = null)
        {
            var BodyParams = new Dictionary<string, string>
            {
                { "SecretKey", Globals.SecretKeyCommunication },
                { "Method", Method.ToString() },
                { "UserId", UserId.ToString() }
            };

            if (VerificationToken != null)
            {
                BodyParams.Add("Token", VerificationToken);
            }

            if (ServerId != null)
            {
                BodyParams.Add("ServerId", ServerId);
            }

            var httpResponseMessage = await httpClient.PostAsync(Globals.KaijuApiWebUrl, new FormUrlEncodedContent(BodyParams));

            var httpResponseString = await httpResponseMessage.Content.ReadAsStringAsync();

            Console.WriteLine($"Request Response: {httpResponseString}" + Environment.NewLine);
            Console.WriteLine($"The request returned with a {httpResponseMessage.StatusCode} status code");

            if (httpResponseMessage.StatusCode == System.Net.HttpStatusCode.InternalServerError)
            {
                return null;
            }

            return JsonConvert.DeserializeObject<KaijuAPIResponseModel>(httpResponseString);
                        
        }
    }
}
