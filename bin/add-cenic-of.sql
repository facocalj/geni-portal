-- -----------------------------------------------------------------
-- Create the entry for CENIC OpenFlow aggregate:
--
-- Execute as:
--
--    psql -U portal -h localhost -f add-sl-of.sql portal
--
-- -----------------------------------------------------------------

insert into service_registry
    (service_type, service_url, service_cert, service_name,
     service_description, service_urn)
  values
    ( -- TYPE: zero = aggregate
      0,
      -- URL
      'https://foam.cenic.net:3626/foam/gapi/2',
      -- CERT
     '/usr/share/geni-ch/sr/certs/cenic-of.pem',
      -- NAME
     'CENIC OpenFlow',
      -- DESCRIPTION
     'CENIC OpenFlow',
      -- URN
     'urn:publicid:IDN+openflow:foam:foam.cenic.net+authority+am'
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
     '/usr/share/geni-ch/sr/certs/cenic-of.pem',
      -- NAME
     '',
      -- DESCRIPTION
     'CENIC OpenFlow cert signer (self)',
      -- URN
     ''
    );
