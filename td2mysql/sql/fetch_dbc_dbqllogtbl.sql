select  
QueryID    queryid                  
,trim(UserName) username                               
,AcctStringDate    acctstringdate                              
 , MaxAMPCPUTime * (HASHAMP()+1) myeffectivecpu
,cast(starttime as varchar(24)) starttime
,cast(FirstRespTime as varchar(24)) lastresptime
,totaliocount as totaliocount
, trim(querytext) querytext 
,sessionid sessionid
from  dbc.DBQLogTbl
where AcctStringDate = date - ?
and   CHARACTER_LENGTH(querytext)>10
and (statementgroup like '%DML%'  or statementgroup like '%DDL Create%'  or statementgroup like  '%Other SysOther%')    
and querytext not like '%DW_DA_CHECKSUM_LOG%'
and querytext not like '%FASTLO%'
and querytext not like '%QUERY_BAND%'
and querytext not like '%TDLOG_TABLES%'
and querytext not like '%ET;'
and querytext not like 'END%'
and querytext not like 'CHECKPOINT%'
and querytext not like '%FASTEXPORT%'
and querytext not like 'select%'
and not ( querytext like 'INSERT%INTO%VALUES%' and querytext not like '%SEL%')
and(querytext like '%del%' or querytext like '%ins%' or querytext like '%upd%' or  querytext like '%create%' or querytext like '%ct%' or querytext like '%merge%' )
and querytext not like 'DATABASE%'
----------------------------Below is the filter you would like to add
and trim(username) like 'B\_%' escape '\'
 
