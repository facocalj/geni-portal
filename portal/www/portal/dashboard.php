<?php
//----------------------------------------------------------------------
// Copyright (c) 2012-2015 Raytheon BBN Technologies
//
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and/or hardware specification (the "Work") to
// deal in the Work without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Work, and to permit persons to whom the Work
// is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Work.
//
// THE WORK IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE WORK OR THE USE OR OTHER DEALINGS
// IN THE WORK.
//----------------------------------------------------------------------
require_once("user.php");
require_once("header.php");
require_once('util.php');
require_once("sr_client.php");
require_once("sr_constants.php");
require_once("pa_client.php");
require_once("pa_constants.php");
require_once('rq_client.php');
require_once("sa_client.php");
require_once("cs_client.php");
require_once("proj_slice_member.php");
include("services.php");

$user = geni_loadUser();
if (!isset($user) || is_null($user) || ! $user->isActive()) {
  relative_redirect('home.php');
}
show_header('GENI Portal: Dashboard', $TAB_HOME);

include("tool-showmessage.php");

?>
<style>
  .dashtext {
    padding: 0px;
    border: none !important;
    margin: 0px !important;
    color: #5F584E !important;
    text-shadow: none !important;
    display: inline;
  }

  .floatright {
    float: right;
  }

  .floatleft {
    float: left;
  }

  .slicebox {
    width: 360px;
    padding: 0px;
    margin: 15px 10px 15px 20px;
    border-radius: 3px;
    border: 2px solid #5F584E;
  }

  .sliceboxinside {
    width: 100%;
  }

  .sliceboxinside table {
    margin: 0px !important;
    box-shadow: none !important;
  }

  #dashboardtools {
    background-color: #F57F21;
    border-radius: 3px;
    padding: 10px;
  }

  select {
    font-family: 'Open Sans';
    font-size: 16px;
    color: #fff;
    background-color: #5F584E;
    padding: 5px;
    border-radius: 0px;
  }

  select:focus {
    outline-width: 0;
  }

  option {
    font-size: 16px;
  }

  option:focus {
      outline-width: 0;
  }

  #projectswitch {
    margin: 20px 15px;
  }

  #sortby {
    margin: 20px 15px;
  }

  .projectinfo {
    margin: 20px;
    font-weight: bold;
  }

  #logtable {
     margin-left: 15px;
  }

  .slicelink:hover {
    cursor: pointer;
    text-decoration: underline;
  }

  .expirationicon {
    height: 16px;
    width: 16px;
    vertical-align: bottom;
    margin-left: 5px; 
  }

  td {
    font-family: 'Open Sans' !important;
  }

  button {
    width: 105px;
  }

  @media (max-width: 600px) {
    .dashtext {
      display: block;
      text-align: center;
      margin: 5px auto !important;
    }

    .floatright, .floatleft {
      float: none;
      margin: 0 auto;
    }

    .slicebox {
      margin: 20px auto;
    }

    #dashboardtools {
      text-align: center;
    }

    #projectcontrols {
      text-align: center;
    }

    #projectswitch {
      margin: 5px 15px;
    }

    .projectinfo {
      margin: 10px;
      text-align: center;
    }

    .slicelink {
      text-decoration: underline;
    }

    #logtable {
      margin-left: 0px;
    }
  }

</style>

<script>
  $(document).ready(function(){

    if (localStorage.lastselection){
      $("#projectswitch").val(localStorage.lastselection);
    }
    if (localStorage.lastsortby){
      $("#lastsortby").val(localStorage.lastselection);
    }
    show_slices($("#projectswitch").val(), $("#sortby").val());

    $("#projectswitch").change(function(){
      show_slices($(this).val(), $("#sortby").val());
    });

    $("#sortby").change(function(){
      show_slices($("#projectswitch").val(), $(this).val());
    });

    $('#ascendingcheck').change(function() {
      sort_slices($("#sortby").val(), this.checked);       
    });


    function show_slices(selection, sortby) {
      $(".slicebox").hide();
      $(".noslices").remove();
      $(".projectinfo").hide();
      localStorage.setItem("lastselection", selection);
      localStorage.setItem("lastsortby", sortby);

      if (is_all(selection)) {
        project_name = "";
        class_name = selection;
        no_slice_msg = "No slices to display.";
      } else {
        project_name = selection;
        class_name = selection + "slices";
        no_slice_msg = "No slices for project" + project_name;
        $("#" + project_name + "info").show();
        $("#" + project_name + "info").css("float", "right");
      }

      $("." + class_name).show();
      sort_slices(sortby, $("#ascendingcheck").prop("checked"));
      if($("." + class_name).length == 0) {
        $("#slicearea").append("<h3 class='dashtext noslices'>" + no_slice_msg + "</h3>");
      }
    }

    function is_all(class_name) {
      return class_name[0] == '-';
    }

    function sort_slices(attr, ascending) {
      numberical_attrs = ['sliceexp', 'resourceexp', 'resourcecount'];
      sorted_slices = $("#slicearea").children().sort(function(a, b) {
        if ($.inArray(attr, numberical_attrs) != -1) { // is it a numerical attribute?
          vA = parseInt($(a).attr(attr));
          vB = parseInt($(b).attr(attr));
        } else {
          vA = $(a).attr(attr);
          vB = $(b).attr(attr); 
        }
        if(ascending) {
          return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
        } else {
          return (vA < vB) ? 1 : (vA > vB) ? -1 : 0;
        }
      });
      $("#slicearea").append(sorted_slices);
    }

  });
</script>

<?php
  $retVal  = get_project_slice_member_info( $sa_url, $ma_url, $user, True);
  $project_objects = $retVal[0];
  $slice_objects = $retVal[1];
  $member_objects = $retVal[2];
  $project_slice_map = $retVal[3];
  $project_activeslice_map = $retVal[4];
?>

<div id="dashboardheader">
  <h1 class="dashtext floatleft">GENI Dashboard</h1>
  <div id="dashboardtools" class="floatright" style="vertical-align: middle; border: 2px solid #5F584E;">
    <h3 class="dashtext" style="color:white !important;">Manage: </h3>
    <button onclick="window.location='profile.php#accountsummary'">Account</button>
    <button onclick="window.location='profile.php#ssh'">SSH Keys</button>
    <button onclick="window.location='profile.php#omni'">Omni</button>
    <button onclick="window.location='profile.php#outstandingrequests'">Requests</button>
  </div>
</div>

<div style="clear:both;">

<?php
  if (count($project_objects) == 0){
    print "no projects";
  } else {
    $lead_names = lookup_member_names_for_rows($ma_url, $user, $project_objects, 
                                                PA_PROJECT_TABLE_FIELDNAME::LEAD_ID);
    $project_options = "<select id='projectswitch'>";
    $project_info = "";
    $show_info = "";
    foreach ($project_objects as $project) {
      $project_id = $project[PA_PROJECT_TABLE_FIELDNAME::PROJECT_ID];
      $project_name = $project[PA_PROJECT_TABLE_FIELDNAME::PROJECT_NAME];
      $lead_id = $project[PA_PROJECT_TABLE_FIELDNAME::LEAD_ID];
      $lead_name = $lead_names[$lead_id];
      $create_slice_button = "<button onClick='window.location=\"createslice.php?project_id=$project_id\"'><b>new slice</b></button>";
      $selected_project = $show_info == "" ? "selected" : "";
      $project_options .= "<option $selected_project value='{$project_name}'>Project: $project_name</option>";
      $project_info .= "<div $show_info class='floatright projectinfo' id='{$project_name}info'>";
      $project_info .= "<b>Project Lead:</b> $lead_name | $create_slice_button</div>";
      $show_info = "style='display:none;'";
    }
    $project_options .= "<option value='-MY-'>All slices I lead</option>";
    $project_options .= "<option value='-THEIR-'>All slices I don't lead</option>";
    $project_options .= "<option value='-ALL-'>All slices</option>";
    $project_options .= "</select>";
    print "<div id='projectcontrols' class='floatleft'><h4 class='dashtext'>Filter by:</h4>$project_options |"; 
    print "<h4 class='dashtext' style='margin-left: 15px !important;'>Sort by:</h4><select id='sortby'><option value='slicename'>Slice name</option><option value='sliceexp'>Slice expiration</option>";
    print "<option value='resourceexp'>Next resource expiration</option></select>";
    print "<input type='checkbox' id='ascendingcheck' value='ascending' checked>Sort ascending<br></div>";
    print $project_info;
  }
?>
</div>
<div id="slicearea" style="clear:both;">

<?php  

  $unexpired_slices = array();
  foreach($slice_objects as $slice) {
    $slice_id = $slice[SA_SLICE_TABLE_FIELDNAME::SLICE_ID];
    $expired = $slice[SA_SLICE_TABLE_FIELDNAME::EXPIRED];
    if(!convert_boolean($expired)) {
      $unexpired_slices[$slice_id] = $slice;
    }
  }
  $slice_objects = $unexpired_slices;

  $slice_owner_names = array();
  if (count($unexpired_slices) > 0) {
    $slice_owner_names = lookup_member_names_for_rows($ma_url, $user, $slice_objects, 
                                                      SA_SLICE_TABLE_FIELDNAME::OWNER_ID);
  }

  function make_slice_box($slice_name, $whose_slice, $slice_url, $lead_name, $project_name, $resource_count, 
                          $slice_exp, $resource_exp, $add_url, $remove_url) {
    print "<div class='floatleft slicebox $whose_slice {$project_name}slices' slicename='$slice_name' sliceexp='$slice_exp' resourceexp='$resource_exp'>";
    print "<div class='sliceboxinside'><table>";
    $resource_exp_icon = "";
    if ($resource_count > 0){
      $plural = $resource_count == 1 ? "" : "s";
      $resource_exp_string = get_time_diff_string($resource_exp);
      $resource_exp_color = get_urgency_color($resource_exp);
      $resource_info = "<b>$resource_count</b> resource{$plural}, next expiration in <b style='color: $resource_exp_color'>$resource_exp_string</b>";
      $resource_exp_icon = "<img class='expirationicon' alt='$resource_exp_color resource expiration icon' src='/common/${resource_exp_color}.png'/>";
    } else {
      $resource_info = "<i>No resources for this slice</i>";
    }
    $slice_exp_string = get_time_diff_string($slice_exp);
    $slice_exp_color = get_urgency_color($slice_exp);
    $slice_info = "Slice expires in <b style='color: $slice_exp_color'>$slice_exp_string</b>";
    $slice_exp_icon = "<img class='expirationicon' alt='$slice_exp_color slice expiration icon' src='/common/${slice_exp_color}.png'/>";
    print "<tr><td colspan='3' style='text-align:center; background-color: #F57F21;'>";
    print "<h5 class='slicelink dashtext' onclick='window.location=\"$slice_url\"' style='color:white !important;'>$slice_name</h5><br>";
    print "<h6 class='dashtext' style='color:white !important;'>lead: $lead_name</h6></td><tr>";
    print "<tr><td style='width:180px;'>$slice_info</td><td style='vertical-align: center;'>$slice_exp_icon</td>";
    print "<td rowspan='2' style='text-align:center; border-left: 1px solid white;'>";
    print "<button onclick='window.location=\"$add_url\"'>Add resources</button><br><button onclick='window.location=\"$remove_url\"'>Remove resources</button></td></tr>";
    print "<tr><td style='border-left: 1px solid white; height:55px;'>$resource_info</td><td style='vertical-align: center;''>$resource_exp_icon</td></tr>";
    print "</table></div></div>";
  }
  
  function get_time_diff_string($num_hours) {
    if ($num_hours < 48) {
      return "$num_hours hours";
    } else {
      $num_days =  $num_hours / 24;
      $num_days = (int) $num_days;
      return "$num_days days";
    }
  }
  
  function get_time_diff($exp_date) {
    $now = new DateTime('now');
    $exp_datetime = new DateTime($exp_date);
    $interval = date_diff($exp_datetime, $now);
    $num_hours = $interval->days * 24 + $interval->h;
    return $num_hours;
  }

  function get_urgency_color($num_hours) {
    if ($num_hours < 24) { 
      return "red";
    } else if ($num_hours < 48) {
      return "orange";
    } else {
      return "green";
    }
  }

  $user_id = $user->account_id;
  foreach ($slice_objects as $slice) {
    $slice_id = $slice[SA_SLICE_TABLE_FIELDNAME::SLICE_ID];
    $slice_urn = $slice[SA_ARGUMENT::SLICE_URN];
    $slice_name = $slice[SA_ARGUMENT::SLICE_NAME];
    $slice_owner_id = $slice[SA_ARGUMENT::OWNER_ID];
    if ($slice_owner_id == $user_id) {
      $whose_slice = "-MY- -ALL-";
    } else {
      $whose_slice = "-THEIR- -ALL-";
    }
    $slice_exp_date = $slice[SA_ARGUMENT::EXPIRATION];
    $args['slice_id'] = $slice_id;
    $query = http_build_query($args);
    $add_resource_url = "slice-add-resources-jacks.php?" . $query;
    $delete_resource_url = "confirm-sliverdelete.php?" . $query;
    $slice_url = "slice.php?" . $query;
    $slice_project_id = $slice[SA_SLICE_TABLE_FIELDNAME::PROJECT_ID];
    if (!array_key_exists($slice_project_id, $project_objects)) {
      $slice_project_name = "-Expired Project-";
    } else {
      $project = $project_objects[ $slice_project_id ];
      $slice_project_name = $project[PA_PROJECT_TABLE_FIELDNAME::PROJECT_NAME];
    }
    $add_slivers_privilege = $user->isAllowed(SA_ACTION::ADD_SLIVERS,
              CS_CONTEXT_TYPE::SLICE, 
              $slice_id);

    $slivers = lookup_sliver_info_by_slice($sa_url, $user, $slice_urn);
    $slice_exp = get_time_diff($slice_exp_date);

    if (count($slivers) == 0) {
      $resource_exp = -999;
    } else {
      $first_sliver = reset($slivers);
      $next_exp = new DateTime($first_sliver[SA_SLIVER_INFO_TABLE_FIELDNAME::SLIVER_INFO_EXPIRATION]);
      foreach ($slivers as $sliver) {
        $this_date = new DateTime($sliver[SA_SLIVER_INFO_TABLE_FIELDNAME::SLIVER_INFO_EXPIRATION]);
        if ($next_exp > $this_date) {
          $next_exp = $this_date;
        }
      }
      $resource_exp = get_time_diff(dateUIFormat($next_exp)); 
    }
    make_slice_box($slice_name, $whose_slice, $slice_url, $slice_owner_names[$slice_owner_id], $slice_project_name,
                   count($slivers), $slice_exp, $resource_exp, $add_resource_url, $delete_resource_url);
  }

  if(count($slice_objects) == 0 ){
    print "<h4 class='dashtext'>No slices</h4>";
  }
?>

</div>

<div style="clear:both;">&nbsp;</div>
<h2 class="dashtext">GENI Messages</h2><br>
<h4 class='dashtext'>Showing logs for the last 
<select id="loglength" onchange="getLogs(this.value);">
  <option value="24">day</option>
  <option value="48">2 days</option>
  <option value="72">3 days</option>
  <option value="168">week</option>
</select></h4>

<script type="text/javascript">
  $(document).ready(function(){
    if(localStorage.loghours){
      $("#loglength").val(localStorage.loghours);
      getLogs(localStorage.loghours);
    } else {
      getLogs("24");
    }
  });
  function getLogs(hours){
    localStorage['loghours'] = hours;
    $.get("do-get-logs.php?hours="+hours, function(data) {
      $('#logtable').html(data);
    });
  }
</script>
<div class="tablecontainer">
  <table id="logtable" style="border: 2px solid #5F584E;"></table>
</div>

<?php

include("footer.php");

?>
