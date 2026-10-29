SELECT concat('DEP;ADDCHILD;DEP_',HST.host_name ,'_SSH;',HST.host_name,',',SVC.service_description)
        FROM centreon.host AS HST
        LEFT JOIN centreon.host_service_relation AS HSR on HSR.host_host_id=HST.host_id
        LEFT JOIN centreon.service as SVC ON SVC.service_id=HSR.service_service_id
        LEFT JOIN centreon.service AS TPL_DEP ON TPL_DEP.service_id=SVC.service_template_model_stm_id
        where HST.host_activate='1'
        AND HST.host_Register='1'
        AND SVC.service_activate='1'
        AND SVC.service_register='1'
        AND TPL_DEP.service_description like 'TS%SSH%'
        AND SVC.service_description not like 'OS-Linux-Process-sshd-Status'
        AND HST.host_name='WORDPRESS'