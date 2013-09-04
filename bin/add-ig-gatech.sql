-- -----------------------------------------------------------------
-- Create the entry for Georgia Tech InstaGENI Rack:
--
-- Execute as:
--
--    psql -U portal -h localhost -f add-ig-gatech.sql portal
--
-- -----------------------------------------------------------------

insert into service_registry
    (service_type, service_url, service_cert, service_name,
     service_description, service_urn)
  values
    ( -- TYPE: zero = aggregate
      0,
      -- URL
      'https://instageni.rnoc.gatech.edu:12369/protogeni/xmlrpc/am/2.0',
      -- CERT
      '/usr/share/geni-ch/sr/certs/ig-gatech-cm.pem',
      -- NAME
      'Georgia Tech InstaGENI',
      -- DESCRIPTION
      'InstaGENI Georgia Tech Rack',
      -- URN
      'urn:publicid:IDN+instageni.rnoc.gatech.edu+authority+cm'
    );

insert into service_registry
    (service_type, service_url, service_cert, service_name,
     service_description, service_urn)
  values
    ( -- TYPE: 7 = CA
      7,
      -- URL
     '',
      -- CERT (self signed)
     '/usr/share/geni-ch/sr/certs/ig-gatech-boss.pem',
      -- NAME
     '',
      -- DESCRIPTION
     'Georgia Tech InstaGENI CA',
      -- URN
     ''
    );
