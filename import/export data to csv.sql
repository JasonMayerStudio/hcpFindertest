USE rise;
SELECT 'hcp_id', 
	'first_name', 
    'm_name', 
    'last_name', 
    'suffix', 
    'address_line1', 
    'address_line2', 
    'city', 
    'state', 
    'zip_code', 
    'phone', 
    'lat', 
    'lng', 
    'link',
    'geocoded address',
	'google formatted address', 
    'location_type', 
    'partial_match'
UNION
SELECT hcp_id, 
	first_name, 
    m_name, 
    last_name, 
    suffix, 
    address_line1, 
    address_line2, 
    city, 
    state, 
    zip_code, 
    phone, 
    hcp.lat, 
    hcp.lng, 
    CONCAT("http://maps.google.com/?daddr=", hcp.lat,",",hcp.lng),
    geo_cache.geocoded_address,
    geo_cache.formatted_address, 
    geo_cache.location_type, 
    geo_cache.partial_match
FROM hcp join geo_cache on hcp.geo_cache_id = geo_cache.id
INTO OUTFILE '/var/lib/mysql/hcps4.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';

