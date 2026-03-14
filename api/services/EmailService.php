<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

/**
 * Gets the Operating System from the user agent string.
 */
function getOS($user_agent) {
    $os_array = [
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    ];
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            return $value;
        }
    }
    return "Unknown OS";
}

const SUPPORT_NOTIFICATION_EMAIL = 'tefinitely@gmail.com';

function sendEmail($recipient, $subject, $body_html, $body_text) {
    global $aws_key, $aws_secret, $aws_region, $sender_email;

    // Use default support email if sender_email is not defined
    $from_email = $sender_email ?? 'support@tefinitely.com';

    $client = new SesClient([
        'version'     => 'latest',
        'region'      => $aws_region,
        'credentials' => [
            'key'    => $aws_key,
            'secret' => $aws_secret,
        ],
    ]);

    try {
        $result = $client->sendEmail([
            'Destination' => [
                'ToAddresses' => [$recipient],
            ],
            'ReplyToAddresses' => ['tefinitely@gmail.com'],
            'Source' => $from_email,
            'Message' => [
              'Body' => [
                  'Html' => [
                      'Charset' => 'UTF-8',
                      'Data' => $body_html,
                  ],
                  'Text' => [
                      'Charset' => 'UTF-8',
                      'Data' => $body_text,
                  ],
              ],
              'Subject' => [
                  'Charset' => 'UTF-8',
                  'Data' => $subject,
              ],
            ],
        ]);
        return true;
    } catch (AwsException $e) {
        // output error message if fails
        error_log($e->getMessage());
        return false;
    }
}
