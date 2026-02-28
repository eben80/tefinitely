<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

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
            'ReplyToAddresses' => [$from_email],
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
