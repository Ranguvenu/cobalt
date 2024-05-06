  <?php
  $capabilities = array(
    'local/opencourses:manage' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
       // 'admin'=>CAP_ALLOW
        )
    ),
  'local/opencourses:create' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    'local/opencourses:view' => array(
        'captype'       => 'write',
        'contextlevel'  => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW, 
        )
    ),
  'local/opencourses:edit' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
             'manager' => CAP_ALLOW,
          'admin'        => CAP_ALLOW  
        )
    ),
      'local/opencourses:delete' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        'manager' => CAP_ALLOW,
          'admin'        => CAP_ALLOW  
        )
    ),
    'local/opencourses:affiliatemooccourses' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
  );
