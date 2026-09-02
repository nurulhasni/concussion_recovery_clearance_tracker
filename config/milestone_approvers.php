<?php

/**
 * Static mapping of recovery milestone stages to the required approver roles to advance into that stage.
 * Standardized across all patients according to CDC HEADS UP and PedsConcussion guidelines.
 */
return [
    2 => ['doctor'],
    3 => ['doctor', 'school'],
    4 => ['doctor', 'school', 'parent'],
];
