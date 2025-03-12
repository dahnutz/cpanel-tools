<?php

// cPanel API Configuration
$cpanel_host = "https://your-cpanel-domain:2087";
$username = "your_whm_username";
$api_token = "your_api_token";

// Function to interact with WHM API
function cpanel_api_request($endpoint, $params = [])
{
    global $cpanel_host, $username, $api_token;
    $url = "$cpanel_host/json-api/$endpoint?api.version=1";

    if (!empty($params)) {
        $url .= "&" . http_build_query($params);
    }

    $headers = [
        "Authorization: whm $username:$api_token",  
        "Accept: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die("cURL Error: " . curl_error($ch));  
    }
    curl_close($ch);

    return json_decode($response, true); 
}

// Fetch all accounts
function get_cpanel_accounts()
{
    $response = cpanel_api_request("listaccts");

    if (!isset($response["data"]["acct"])) {
        die("Failed to fetch account list! Check API credentials.");
    }

    return $response["data"]["acct"];
}

// Process data
$accounts = get_cpanel_accounts();
$table_data = [];
$total_quota = 0;
$total_used = 0;

foreach ($accounts as $account) {
    $username = $account["user"];
    $domain = $account["domain"];
    $package = isset($account["plan"]) ? $account["plan"] : "N/A";  
    $quota = isset($account["disklimit"]) ? intval(str_replace("M", "", $account["disklimit"])) : "N/A";
    $used = isset($account["diskused"]) ? intval(str_replace("M", "", $account["diskused"])) : "N/A";

    if (is_numeric($quota) && is_numeric($used)) {
        $percent_used = $quota > 0 ? round(($used / $quota) * 100, 2) . "%" : "N/A";
        $total_quota += $quota;
        $total_used += $used;
    } else {
        $percent_used = "N/A";
    }

    $table_data[] = [$username, $domain, $package, $quota, $used, $percent_used];
}

// Total row (fixed position at the bottom)
$total_percent = $total_quota > 0 ? round(($total_used / $total_quota) * 100, 2) . "%" : "N/A";
$total_row = ["TOTAL", "-", "-", $total_quota, $total_used, $total_percent];

// ✅ Sort the table data by "Usage (%)" descending
usort($table_data, function ($a, $b) {
    $a_val = floatval(str_replace("%", "", $a[5]));
    $b_val = floatval(str_replace("%", "", $b[5]));
    return $b_val <=> $a_val;  // Sort descending
});

// ✅ Append TOTAL row at the end (always stays at the bottom)
$table_data[] = $total_row;

// ✅ Handle CSV download before any HTML output
if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="cpanel_disk_usage.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ["Username", "Domain", "Package Used", "Quota (MB)", "Used (MB)", "Usage (%)"]);

    foreach ($table_data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cPanel Disk Usage</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background-color: #f4f4f9; padding: 20px; }
        h2 { color: #333; }

        /* Auto-size table width based on content */
        table { 
            margin: 20px auto; 
            border-collapse: collapse; 
            width: auto;
            background: #fff; 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 90%;
            min-width: 500px;
        }
        
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; cursor: pointer; }
        th:hover { background-color: #0056b3; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .total-row { font-weight: bold; background-color: #007bff !important; color: white; }
        .download { margin-top: 20px; }
        button { border: none; background: #007bff; color: white; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
    <script>
        function downloadCSV() {
            window.location.href = "?download=csv";
        }
    </script>
</head>
<body>

<h2>cPanel Disk Usage Report</h2>

<table id="cpanelTable">
    <tr>
        <th onclick="sortTable(0)">Username</th>
        <th onclick="sortTable(1)">Domain</th>
        <th onclick="sortTable(2)">Package Used</th>
        <th onclick="sortTable(3)">Quota (MB)</th>
        <th onclick="sortTable(4)">Used (MB)</th>
        <th onclick="sortTable(5)">Usage (%)</th>
    </tr>
    <?php foreach ($table_data as $row): ?>
    <tr class="<?php echo ($row[0] === 'TOTAL') ? 'total-row' : ''; ?>">
        <td><?php echo htmlspecialchars($row[0]); ?></td>
        <td><?php echo htmlspecialchars($row[1]); ?></td>
        <td><?php echo htmlspecialchars($row[2]); ?></td>
        <td><?php echo htmlspecialchars($row[3]); ?></td>
        <td><?php echo htmlspecialchars($row[4]); ?></td>
        <td><?php echo htmlspecialchars($row[5]); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<div class="download">
    <button onclick="downloadCSV()">Download CSV</button>
</div>

</body>
</html>