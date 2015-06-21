<?php
	define("DEBUG", true); // Use these anytime you are running a debug on a function, to make it easier to search fore.
	
	define("MALE",1);
	define("FEMALE",2);
	
	// STAFF CLASSES STUFF
	define("NAME_STANDARD",1);
	define("NAME_INITIALS",2);
	define("NAME_FIRST_L",3);
	define("NAME_F_LAST",4);
	define("NAME_FIRST",5);
	define("NAME_LAST",6);
	
	define("AWAY_ARRDEP",1);
	define("AWAY_VAC",2);
	define("AWAY_DAY_OFF",3);
	define("AWAY_OTHER",4);
	
	define("SHIFT_PAR",1);
	define("SHIFT_INST",2);
	define("NULL_DATE",'0000-00-00');
	
	define("TEMP_AV_STAFF",1);
	define("TEMP_AV_TYPE",2);
	define("TEMP_DEFAULT",'default');
	
	// AUTHENTICATION STUFF
	define("AUTH_ADMIN",1);
	define("AUTH_GENERAL",2);
	define("AUTH_FREE",3);
	
	// CALENDAR STUFF
	define("CAL_STAFF",1);
	define("CAL_POS",2);
	define("CAL_SHIFT",3);
	define("CAL_DATE",4);
	define("CAL_DAY",5);
	
	define("CAL_TYPE_DAY",1);
	define("CAL_TYPE_WEEK",2);
	define("CAL_TYPE_MONTH",3);
	
	// AIRPORT STUFF
	define("AIR_TYPE_DRIVER",1);
	define("AIR_TYPE_PASSENGER",2);
	define("AIR_TYPE_ARR",1);
	define("AIR_TYPE_DEP",2);
	
	// ROTA STUFF
	define("ROTA_STD",1);
	define("ROTA_STAFF",2);
	
	define("ROTA_SHEET_IP",1);
	
	// DATESTR STUFF
	define("DATESTR_FORMAT_STD","Y-m-d");
	define("DATESTR_FORMAT_DAY","w");
	define("DATESTR_FORMAT_DJS","D jS");
	
	define("DATESTR_ARRAY_MONTH", 1);
	define("DATESTR_ARRAY_WEEK", 2);
	define("DATESTR_ARRAY_MONTH_EXACT", 3);
	define("DATESTR_ARRAY_WEEK_EXACT", 4);
	define("DATESTR_ARRAY_MONTH_EXCLUDE", 5);
	define("DATESTR_ARRAY_WEEK_EXCLUDE", 6);
	define("DATESTR_ARRAY_MONTH_INCLUDE", 7);
	define("DATESTR_ARRAY_WEEK_INCLUDE", 8);
	
?>