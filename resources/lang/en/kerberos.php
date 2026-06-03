<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notif' => [
        'unknown_attempt' => [
            'subject' => '⚠️ Unknown Kerberos login attempt - :app',
            'greeting' => 'Unknown Kerberos identifier detected',
            'line_detected' => 'An authentication attempt was made with an unknown Kerberos identifier.',
            'line_legit' => 'If this is a legitimate user, add their Kerberos identifier to the system.',
            'salutation' => '— :app',
        ],
        'new_request' => [
            'subject' => '📋 New access request - :app',
            'greeting' => 'New access request received',
            'line_details' => 'A user has requested access to the application.',
        ],
        'rejected' => [
            'subject' => '❌ Access request rejected - :app',
            'greeting' => 'Your access request has been rejected.',
            'line_reason' => 'Reason: :reason',
            'line_retry' => 'You can submit a new request with additional justification if necessary.',
            'action' => 'Submit a new request',
        ],
        'accepted' => [
            'subject' => '✅ Access request approved - :app',
            'greeting' => 'Your access request has been approved!',
            'line_role' => 'Role assigned: **:role**',
            'line_message' => "Administrator's message: :message",
            'action' => 'Sign in',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash messages
    |--------------------------------------------------------------------------
    */

    'flash' => [
        'error_empty_kerberos' => 'Please enter or select a Kerberos identifier.',
        'simulation_disabled' => 'Simulation disabled.',
        'error_generic' => 'An error occurred. Please try again.',
        'simulation_banner_logout' => 'Simulation disabled. Please log in again.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    'validation' => [
        'justification_required' => 'You must provide a justification for your access request.',
        'justification_min' => 'The justification must be at least 20 characters.',
        'justification_max' => 'The justification cannot exceed 500 characters.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Views — Simulate Kerberos
    |--------------------------------------------------------------------------
    */

    'simulate' => [
        'dev_mode' => '⚠️ Development Mode',
        'env_info' => 'Kerberos simulation active (environment :env)',
        'active_label' => 'Simulation active',
        'disable' => 'Disable',
        'enable_section' => 'Enable simulation',
        'custom_label' => 'Custom Kerberos identifier',
        'custom_placeholder' => 'firstname.lastname@example.com',
        'custom_hint' => 'Enter any Kerberos identifier',
        'or' => 'or',
        'select_label' => 'Select an existing user',
        'select_placeholder' => 'Choose an existing identifier...',
        'select_hint' => 'First 10 identifiers from the database',
        'simulate_button' => 'Simulate login',
        'simulating' => 'Logging in...',
        'warning' => '<strong>Warning:</strong> This simulation mode is <strong>strictly reserved for development and staging environments</strong>. It is automatically disabled in production.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Views — Access Denied
    |--------------------------------------------------------------------------
    */

    'access_denied' => [
        'title' => 'Access Denied',
        'subtitle' => 'Your Kerberos identifier is not recognised',
        'unknown_id' => 'Identifier not recognised',
        'not_registered' => 'The following Kerberos identifier is not registered in our system:',
        'admins_notified' => 'Administrators have been automatically notified of this login attempt. If you believe this is an error, please contact your IT department.',
        'what_to_do' => 'What should you do?',
        'tip_network' => 'Make sure you are connected to the company network',
        'tip_it' => 'Contact your IT department to verify your account',
        'tip_local' => 'Use the standard login form if you have a local account',
        'back_button' => 'Back to login page',
        'attempt_time' => 'Attempt at :datetime',
    ],

    /*
    |--------------------------------------------------------------------------
    | Views — Request Access
    |--------------------------------------------------------------------------
    */

    'request_access' => [
        'title' => 'Access Request',
        'subtitle' => 'Your account has no role yet. Please fill in this form.',
        'sent_title' => 'Access request sent',
        'sent_subtitle' => 'Your request has been forwarded to the administrators',
        'sent_body' => 'Your access request has been sent to the administrators.',
        'sent_notification' => 'You will be notified by email once your request has been processed.',
        'no_role_title' => 'Account without role',
        'no_role_body' => 'Your Kerberos identifier <strong>:kerberos</strong> is recognised, but your account has no role assigned. Please justify your access request below.',
        'kerberos_label' => 'Kerberos identifier',
        'kerberos_hint' => 'Your Kerberos identifier detected automatically',
        'justification_label' => 'Justification for your request',
        'justification_placeholder' => 'Explain why you need access to the application (minimum 20 characters)...',
        'justification_hint' => 'Minimum 20 characters, maximum 500 characters',
        'admin_info' => 'Administrators will receive your request by email and you will be notified once it has been processed.',
        'submit_button' => 'Send access request',
        'submitting' => 'Sending...',
        'cancel_button' => 'Back to login',
        'back_button' => 'Back to login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Views — Simulation Banner
    |--------------------------------------------------------------------------
    */

    'simulation_banner' => [
        'mode' => 'Simulation Mode',
        'quit' => 'Quit',
        'description' => 'You are logged in simulation mode (:env). Click "Quit" to log out and disable simulation.',
        'disable_title' => 'Disable simulation',
    ],

];
