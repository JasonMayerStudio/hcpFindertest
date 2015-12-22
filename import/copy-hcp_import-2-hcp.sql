DROP Table rise.hcp;
CREATE TABLE rise.hcp LIKE rise.hcp_import; 
INSERT rise.hcp SELECT * FROM rise.hcp_import;