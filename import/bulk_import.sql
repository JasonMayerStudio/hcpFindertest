USE rise;

LOAD DATA INFILE '/var/www/vhosts/dev1/docroot/import/bup_providers_data-pipe-20150828.txt' 
	INTO TABLE hcp_import 
    FIELDS 
		TERMINATED BY '|' 
        OPTIONALLY ENCLOSED BY '\"' 
        ESCAPED BY ''
	LINES TERMINATED BY '\n' 
    IGNORE 2 LINES 
    (first_name,m_name,last_name,suffix,address_line1,address_line2,city,state,zip_code,phone);