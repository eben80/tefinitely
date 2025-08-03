<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

function sendEmail($recipient, $subject, $body_html, $body_text) {
    // TODO: Replace with your AWS credentials and region
    $aws_key = 'AKIA5SGXHWFP2UEGBIO7';
    $aws_secret = 'YOUR_AWS_SECRET_ACCESS_KEY';
    $aws_region = 'us-east-1'; // e.g., 'us-east-1'

    // TODO: Replace with a sender email address that you have verified with Amazon SES.
    $sender_email = 'tefinitely@gmail.com';

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
            'ReplyToAddresses' => [$sender_email],
            'Source' => $sender_email,
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
