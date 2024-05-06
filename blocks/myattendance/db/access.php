<?php
    $capabilities = array(

    'block/myattendance:myaddinstance' => array(
          'captype' => 'write',
          'contextlevel' => CONTEXT_SYSTEM,
          'archetypes' => array(
          'user' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),
    'block/myattendance:addinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),
);
