<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function validation_config()
{
    return [
        'name' => 'Domain Contact Validation for WHMCS',
        'description' => 'Validates and confirms domain registrant contact details to ensure compliance with ICANN regulations.',
        'version' => '1.0',
        'author' => 'Namingo',
        'fields' => [],
    ];
}

function validation_activate()
{
    return [
        'status' => 'success',
        'description' => 'Validation module activated successfully.',
    ];
}

function validation_deactivate()
{
    return [
        'status' => 'success',
        'description' => 'Validation module deactivated successfully.',
    ];
}

function validation_clientarea($vars)
{
    $message = '';
    $isError = false;
    $template = $vars['template'];

    // Access GET parameters and sanitize the token
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($token) {
        try {
            // Look up token in database
            $client = Capsule::table('namingo_contact')
                ->where('validation_log', $token)
                ->first();

            // If token is found and not yet validated, update database and display success message
            if ($client && $client->validation == 0) {
                $contact_id = $client->id;

                // Update 'validation' field to 1 (validated)
                Capsule::table('namingo_contact')
                    ->where('id', $contact_id)
                    ->update(['validation' => 1]);

                $message = 'Contact information validated successfully!';
            } else {
                $message = 'Error: Invalid or already validated validation token.';
                $isError = true;
            }
        } catch (\Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $isError = true;
        }
    } else {
        $message = 'Please provide a validation token.';
        $isError = true;
    }

    return [
        'pagetitle' => 'Contact Validation',
        'breadcrumb' => ['index.php?m=validation' => 'Contact Validation'],
        'templatefile' => 'clientarea',
        'requirelogin' => false,
        'vars' => [
            'message' => $message,
            'isError' => $isError,
            'template' => $template,
        ],
    ];
}
