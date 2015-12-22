

###Prequisites: 

deploy the HCP Data Manager project to the dev1 folder of the vagrant project, once done you should be able to get to the data management pages here:
http://dev1.orexo.vagrant.evokeclients.com/data

all these toold work against the 'hcp_import' table.

This project runs off the rise DB, so if you have already set up rise, you should be ok, but if you need to set it, import the sql from the the rise-us-com/sql/rise-vagrant.sql 

this app expects the following DB creds:
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'rise'),
            'username'  => env('DB_USERNAME', 'rise'),
            'password'  => env('DB_PASSWORD', 'rise'),

if you need to change them they are in /docroot/app/config/database.php

Connecting to the DB in vagrant

Connect via ssh using these settings:
SSH Host: 192.168.56.222:22
SSH user: vagrant
use key based authentication using this keyfile (in the orexo vagant project:
/Orexo_Vagrant/trunk/vagrant/puphpet/files/dot/ssh/insecure_private_key

mysql host: 127.0.0.1
mysql user: root
mysql pass: 123


###Importing SAMSHA data

1. Download the data as Pipe Delimited file from
http://buprenorphine.samhsa.gov/pls/bwns_locator/!provider_search.process_query?alternative=CHOICED

2. Move the file into a folder accessable by the vagrant box
ie. docroot/import so it would be avaialble inside the vagant box
here: /var/www/vhosts/dev1/docroot/import/bup_providers_data-pipe.txt

3. Empty the hcp_import table, if you load the admin page, http://dev1.orexo.vagrant.evokeclients.com/data, clicking the 'truncate' link will do this 

4. use the bulk_import.sql statement to import the data into the 'hcp_import' table (if needed adjust the path the the file in line #3)

5. take a look at output, '1265 Data truncated for column 'zip_code' at row YYY' warnings are thrown when the zipcode was in the format of xxxxx-xxxx, our DB only holds xxxxx, they can usuallt be ignored. You can confirm by looking at the same line number in the import file (actually row number-2 since the import skips the first 2 rows)
 
'1262 Row 7 was truncated; it contained more data than there were input columns' appear to be generated since the data now includes 2 new flags, but the import seems ok.

6. click the View All link in the admin page and you should be able to browse the imported data.

8. before running the geocode all, empty the log file /storage/log/laravel.log 

9. click Geocode All to perform geocodeing, this will check for the address in the geo_cache table so hopefully most geocodes will be there, anything not foudn inthe geo_cache table will be sent to google for geocoding.

note: line# 225 of /docroot/app/Http/Controllers/HcpDataManager/Main.php
$not_geocoded =  HcpImport::batchGeocode(300); 
300 is the max number of requests it will send to google, you can keep clicking the geocode all and it will pick up where it left off with the geocoding.
This process is also limited by line number #24
$hcps = DB::table(static::$table)->where('geo_cache_id', null)->take(5000)->get();

SO basically it takes 5000 HCPs and then tries to geocode them, it will stop if it hits 300 geocodes that need to be sent to google.
You can just click Geocode All to continue

Geocoding takes some time, there is no progess indicator, just look for the page loading icon to fiinsh in your browser.

When it finishes the batch it is workingon, it will output any HCPs that google was not able to geocode.
If there is nothing listed, then it got a geocode for all the addresses it tried.

After running the geocode, take a look at the log file /storage/log/laravel.log and you can see the status of the coding
You can search for the string 'Address NOT found in geo_cache' to see how many new geocodes were required.

10. once you have things geocded as you want, copy the hcp_import table to the hcp table so it is used as the source for the rise site.
You can use the '/docroot/import/copy-hcp_import-2-hcp.sql' statement to do this

11. when deploying to production just deply the hcp table.

