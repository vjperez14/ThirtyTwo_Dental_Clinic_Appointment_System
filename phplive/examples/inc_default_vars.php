<?php
	/*****************************************************
	/*
	/* DO NOT MODIFY OR DELETE THIS FILE
	/* It is used for variable population in few areas.  This file will be
	/* replaced with each software upgrade.  There is not a need to update
	/* these variables here.
	/*
	*****************************************************/

	$DEFAULT_VAR_OFFLINE_TEMPLATE_SUBJECT = "%%visitor_subject%%" ;
	$DEFAULT_VAR_OFFLINE_TEMPLATE_BODY = "--------------------------
Message to: %%department_name%%
--------------------------

%%visitor_message%%

======= Visitor Information =======

%%custom_variables%%

Name : %%visitor%%
Email : %%visitor_email%%

Footprints : %%stat_total_footprints%%
IP Address : %%stat_ip%%
Visitor ID : %%stat_visitor_id%%
Clicked From : %%stat_onpage_url%%

======
$LANG[MSG_EMAIL_FOOTER]

" ;
?>