SELECT concat('DEP;ADD;DEP_',HST.host_name ,'_SNMP;DEP_',HST.host_name,'_PING;SERVICE;',HST.host_name,',',SVC.service_description)
        FROM centreon.host AS HST
        LEFT JOIN centreon.host_service_relation AS HSR on HSR.host_host_id=HST.host_id
        LEFT JOIN centreon.service as SVC ON SVC.service_id=HSR.service_service_id
        where HST.host_activate='1'
        AND HST.host_Register='1'
        AND SVC.service_activate='1'
        AND SVC.service_register='1'
        AND SVC.service_description like '%Os-Linux-Process-snmpd-Status'
        AND SVC.service_description not like '%mib%'
        AND HST.host_name='CENTREON-CENTRAL-OMR'