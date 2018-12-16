using System;
using System.IO;
using System.Net;
using System.Net.Sockets;
using System.Threading;
using NAudio.CoreAudioApi;
using WebSocketSharp;
using WebSocketSharp.Server;

namespace SoundCloudRemoteSite
{
    class Program
    {
        public static string GetLocalIPAddress()
        {
            var host = Dns.GetHostEntry(Dns.GetHostName());
            foreach (var ip in host.AddressList)
            {
                if (ip.AddressFamily == AddressFamily.InterNetwork)
                {
                    return ip.ToString();
                }
            }
            throw new Exception("No network adapters with an IPv4 address in the system!");
        }

        private static MMDeviceEnumerator _deviceEnumerator = new MMDeviceEnumerator();
        private static MMDevice _playbackDevice;

        public static void SystemVolumeConfigurator()
        {
            _playbackDevice = _deviceEnumerator.GetDefaultAudioEndpoint(DataFlow.Render, Role.Multimedia);
        }
        
        public static int GetVolume()
        {
            return (int)(_playbackDevice.AudioEndpointVolume.MasterVolumeLevelScalar * 100);
        }

        public static void SetVolume(int volumeLevel)
        {
            if (volumeLevel < 0 || volumeLevel > 100)
                throw new ArgumentException("Volume must be between 0 and 100!");

            _playbackDevice.AudioEndpointVolume.MasterVolumeLevelScalar = volumeLevel / 100.0f;
        }

        public class Mobile : WebSocketBehavior
        {
            protected override void OnOpen()
            {
                Console.WriteLine("[MOBILE] Connected");
            }

            protected override void OnMessage(MessageEventArgs e)
            {
                if (e.Data == "volume")
                {
                    SystemVolumeConfigurator();
                    this.Send("volume:" + GetVolume());
                    return;
                }
                if (e.Data.StartsWith("setvolume:"))
                {
                    SetVolume(int.Parse(e.Data.Split(':')[1]));
                    Console.WriteLine("[MOBILE] Volume: " + GetVolume());
                    return;
                }
                desktop.WebSocketServices.Broadcast(e.Data);
            }

            protected override void OnClose(CloseEventArgs e)
            {
                Console.WriteLine("[MOBILE] Disconnected");
            }
        }

        public class Desktop : WebSocketBehavior
        {
            protected override void OnOpen()
            {
                Console.WriteLine("[DESKTOP] Connected");
            }

            protected override void OnMessage(MessageEventArgs e)
            {
                mobile.WebSocketServices.Broadcast(e.Data);
            }

            protected override void OnClose(CloseEventArgs e)
            {
                Console.WriteLine("[DESKTOP] Disconnected");
            }
        }

        static WebSocketServer mobile = new WebSocketServer(IPAddress.Parse(GetLocalIPAddress()), 15768);
        static WebSocketServer desktop = new WebSocketServer(IPAddress.Parse("127.0.0.1"), 15769);

        static void Main(string[] args)
        {
            Console.Title = "SoundCloud Server";
            SystemVolumeConfigurator();
            new Thread(() =>
            {
                HttpListener listener = new HttpListener();
                listener.Prefixes.Add("http://localhost:80/");
                listener.Prefixes.Add("http://" + GetLocalIPAddress() + ":80/");
                listener.Start();
                while (true)
                {
                    HttpListenerContext context = listener.GetContext();
                    HttpListenerResponse response = context.Response;
                    Console.WriteLine("[MOBILE] Player opened");
                    byte[] buffer = Properties.Resources.index;
                    response.ContentLength64 = buffer.Length;
                    Stream output = response.OutputStream;
                    output.Write(buffer, 0, buffer.Length);

                    output.Close();
                }
            }).Start();

            mobile.AddWebSocketService<Mobile>("/soundcloud/mobile");
            desktop.AddWebSocketService<Desktop>("/soundcloud/desktop");

            mobile.Start();
            desktop.Start();
            
            Console.Read();
        }
    }
}
