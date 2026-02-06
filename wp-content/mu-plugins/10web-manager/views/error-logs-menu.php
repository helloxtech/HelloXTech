<?php


function tenweb_sort_logs_date($a, $b)
{
  if ( $a['date'] == $b['date'] ) {
    return 0;
  }
  return ( $a['date'] < $b['date'] ) ? 1 : -1;
}

function tenweb_sort_logs_timestamp($a, $b)
{
  if ( $a['timestamp'] == $b['timestamp'] ) {
    return 0;
  }
  return ( $a['timestamp'] <= $b['timestamp'] ) ? 1 : -1;
}

if (isset($is_migration)) {
    uasort($logs, "tenweb_sort_logs_timestamp");
} else {
    uasort($logs, "tenweb_sort_logs_date");
}



?>
<style>
    table {
        width: 95%;
        border: 1px solid black;
        border-collapse: collapse;
    }

    td, th {
        border: 1px solid black;
    }

    td:nth-child(1) {
        width: 25%;
    }

    td:nth-child(2) {
        width: 60%;
    }

    td:nth-child(3) {
        width: 10%;
    }

    button {
        padding: 6px;
        margin-bottom: 6px;
    }
</style>

<h2><?php echo "Timezone: " . $time_zone; ?></h2>

<?php if (!isset($is_migration)) { ?>
<button id="tenweb_clear_logs">Clear Logs</button>
<button id="tenweb_clear_cache">Clear Cache</button>
<button id="tenweb_check_curl">Check Curl</button>
<?php } else {?>
<button id="tenweb_clear_migration_logs">Clear Logs</button>
<?php }?>

<table>
    <thead>
    <th>Key</th>
    <th>Log</th>
    <th>Date</th>
    </thead>
    <tbody>
    <?php foreach ($logs as $key => $log) { ?>
        <tr <?php echo (isset($log['success']) && $log['success'] == 0) ? 'class=tenweb_migrate_log_error' : '' ?>>
            <td><?php echo $key; ?></td>
            <td><?php echo $log['msg']; ?></td>
            <td>
              <?php
              if ( isset($is_migration) ) {
                  $now = \DateTime::createFromFormat('U.u', $log['timestamp']);
                  if( $now ) {
                    $date = $now->format("m-d-Y H:i:s.u");
                    echo "<span title='" . $date . "'>" . $now->format("m-d-Y H:i:s") . "</span>";
                  }
              } else {
                  echo $log['date'];
              }
              ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>