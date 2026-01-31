<?php
/*
  Visitor Logger
  Safe with environment variable secrets
  Author: Jeroenimo02 (fixed by ChatGPT)
*/

date_default_timezone_set("Europe/Amsterdam");
$DateTime = date('d/m/Y H:i:s');

// Get visitor info
$IP = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
$Browser = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Block bots
if (preg_match('/bot|discord|curl|spider|crawler|^$/i', $Browser)) exit;

// GeoIP lookups
$Details = @json_decode(file_get_contents("http://ip-api.com/json/{$IP}"));
$VPNConn = @json_decode(file_get_contents("https://json.geoiplookup.io/{$IP}"));

$VPN = ($VPNConn->connection_type ?? '') === "Corporate" ? "Yes" : "No";
$Country = $Details->country ?? 'Unknown';
$CountryCode = strtolower($Details->countryCode ?? '');
$Region = $Details->regionName ?? 'Unknown';
$City = $Details->city ?? 'Unknown';
$Lat = $Details->lat ?? '0';
$Lon = $Details->lon ?? '0';
$Flag = $CountryCode ? "https://countryflagsapi.com/png/{$CountryCode}" : null;

// Discord logger class
class Discord
{
    public function Visitor()
    {
        global $IP, $Browser, $VPN, $Country, $Region, $City, $Lat, $Lon, $Flag, $DateTime;

        $Webhook = getenv('DISCORD_WEBHOOK_URL'); // ✅ load secret
        if (!$Webhook) {
            error_log("Discord webhook not set!");
            return;
        }

        $embed = [
            "username" => "Visitor Logger",
            "avatar_url" => $Flag,
            "embeds" => [[
                "title" => "Visitor from {$Country}",
                "color" => 3447003, // clean blue
                "description" => "**Region:** {$Region}\n**City:** {$City}\n**VPN:** {$VPN}",
                "fields" => [
                    ["name" => "IP Address", "value" => $IP, "inline" => true],
                    ["name" => "Browser", "value" => $Browser, "inline" => true],
                    ["name" => "Map", "value" => "[View Location](https://www.google.com/maps/search/?api=1&query={$Lat},{$Lon})"]
                ],
                "footer" => [
                    "text" => $DateTime,
                    "icon_url" => "https://cdn-icons-png.flaticon.com/512/992/992700.png"
                ]
            ]]
        ];

        $ch = curl_init($Webhook);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($embed),
            CURLOPT_TIMEOUT => 5
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}

// Run on page load
(new Discord)->Visitor();
