<?php

/**
 * Static mapping of recovery milestone stages and Stage 3 activity steps to restriction codes.
 * Standardized across all patients according to CDC HEADS UP and PedsConcussion guidelines.
 */
return [
    1 => ['complete_rest', 'no_screen_time'],
    2 => ['limited_screen_time', 'no_homework'],
    3 => [
        1 => ['no_contact_sports', 'light_activity_only'],
        2 => ['no_contact_sports', 'moderate_activity'],
        3 => ['no_contact_sports', 'moderate_activity'],
        4 => ['light_contact_only'],
        5 => ['light_contact_only'],
        6 => [],
    ],
    4 => [],
];
