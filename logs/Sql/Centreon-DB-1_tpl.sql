SELECT TPL.host_name
        FROM centreon.host as HST
        LEFT JOIN centreon.host_template_relation HTR ON HTR.host_host_id=HST.host_id
        LEFT JOIN centreon.host as TPL ON TPL.host_id=HTR.host_tpl_id
        WHERE HST.host_name='Centreon-DB-1'
        AND TPL.host_name like 'TH_Net%'