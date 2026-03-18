<?php

require '../vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

$client = new Client();
$client->setAuthConfig('./google/calendarApi.json');
$client->addScope(Calendar::CALENDAR);

// Impersonate a user (make sure this user is within your domain if domain-wide delegation is enabled)
$client->setSubject('revanthshiva3@gmail.com');

$service = new Calendar($client);

$event = new Event([
    'summary' => 'Google I/O 2023',
    'location' => '800 Howard St., San Francisco, CA 94103',
    'description' => 'A chance to hear more about Google\'s developer products.',
    'start' => [
        'dateTime' => '2023-05-28T09:00:00-07:00',
        'timeZone' => 'America/Los_Angeles',
    ],
    'end' => [
        'dateTime' => '2023-05-28T17:00:00-07:00',
        'timeZone' => 'America/Los_Angeles',
    ],
    'attendees' => [
        ['email' => 'cyberwarning03@gmail.com'],
    ],
]);

try {
    $event = $service->events->insert('primary', $event);
    printf('Event created: %s\n', $event->htmlLink);
} catch (Google\Service\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

