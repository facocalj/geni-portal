<?php

require_once('message_handler.php');
require_once('db_utils.php');
require_once('sr_constants.php');
require_once('response_format.php');

/**
 * GENI Clearinghouse Service Registry (SR) controller interface
 * The Service Registry maintains a list of services registered
 * with the clearinghouse, and their type, URL and certificate (signed
 * by the SR itself.
 * 
 * Supports 4 interfaces:
 * get_services
 * get_services_of_type
 * register_service
 * remove_service	      
 *
 **/

/* Get all services currently registered with SR 
 * Args: None
 * Return: List of services
 */
function get_services($args)
{
  global $SR_TABLENAME;
  // error_log("listing all services");

  $query = "SELECT * FROM " . $SR_TABLENAME;
  // error_log("SR.GS QUERY = " . $query);
  $result = db_fetch_rows($query, "SR.get_services");
  //  $rows = $result[RESPONSE_ARGUMENT::VALUE];
  return $result;
}

/* Get all services of given type currently registered with SR 
 * Args: Service type
 * Return: List of services of given type
 */
function get_services_of_type($args)
{
  global $SR_TABLENAME;
  $service_type = $args[SR_ARGUMENT::SERVICE_TYPE]; 
  // error_log("listing services of type " . $service_type);

  $query = "SELECT * FROM " . $SR_TABLENAME . " WHERE " . 
    SR_TABLE_FIELDNAME::SERVICE_TYPE . 
    " = '" . $service_type . "'";
  //  error_log("SR.GSOT QUERY = " . $query);
  $result = db_fetch_rows($query, "SR.get_services_of_type");
  //  $rows = $result[RESPONSE_ARGUMENT::VALUE];
  // error_log("ROWS = " . $rows);
  //  error_log("SR.GSOT RESULT = " . print_r($result, true));
  return $result;
}

/* Get the service with the given id.
 * Args: Service id
 * Return: List of services with that id or an empty list of no services match.
 */
function get_service_by_id($args)
{
  global $SR_TABLENAME;
  $service_id = $args[SR_ARGUMENT::SERVICE_ID];
  $id_column = SR_TABLE_FIELDNAME::SERVICE_ID;
  $conn = db_conn();
  $query = "SELECT * FROM $SR_TABLENAME WHERE"
    . " $id_column = " . $conn->quote($service_id, 'integer');
  $result = db_fetch_rows($query, "SR.get_service_by_id");
  return $result;
}

/*
 * Register service of given type and given URL
 * *** TO DO: Create certificate (and key pair) for service
 * Args: Service Type, Service URL
 * Return : Success/failure
 */
function register_service($args)
{
  global $SR_TABLENAME;
  $service_type = $args[SR_ARGUMENT::SERVICE_TYPE];
  $service_url = $args[SR_ARGUMENT::SERVICE_URL];
  // error_log("register service $service_type $service_url");
  $stmt = "INSERT INTO " . $SR_TABLENAME . "(" . 
    SR_TABLE_FIELDNAME::SERVICE_TYPE . ", " . 
    SR_TABLE_FIELDNAME::SERVICE_URL . ") VALUES (" . 
    "'" . $service_type . "'" . 
    ", ". 
    "'" . $service_url . "')";
  // error_log("SR.RegisterService STMT = " . $stmt);
  $result = db_execute_statement($stmt, "SR.register_service");
  return $result;
}
/*
 * Remove a service of given type and given URL from SR 
 * Args: Service Type, Service URL
 * Return : Success/failure
 */
function remove_service($args)
{
  global $SR_TABLENAME;
  $service_type = $args[SR_ARGUMENT::SERVICE_TYPE];
  $service_url = $args[SR_ARGUMENT::SERVICE_URL];
  // error_log("remove service $service_type $service_url");
  $stmt = "DELETE FROM " . $SR_TABLENAME . " WHERE " . 
    SR_TABLE_FIELDNAME::SERVICE_TYPE . " = '" . 
    $service_type . "' " . 
    " AND " . 
    SR_TABLE_FIELDNAME::SERVICE_URL . " = '" .
    $service_url . "'";
  // error_log("SR.RemoveService STMT = " . $stmt);
  $result = db_execute_statement($stmt, "SR.remove_service");
  return $result;
}

handle_message("SR");

?>

