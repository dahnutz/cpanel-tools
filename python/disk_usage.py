import requests
import pandas as pd
import urllib3

# Suppress SSL warnings (optional, but not recommended)
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Configuration (Replace with your cPanel details)
CPANEL_HOST = "https://your-cpanel-domain:2087"  # Replace with your WHM/cPanel URL
USERNAME = "your_whm_username"
API_TOKEN = "your_api_token"

HEADERS = {
    "Authorization": f"whm {USERNAME}:{API_TOKEN}"
}

# Function to get account list
def get_cpanel_accounts():
    url = f"{CPANEL_HOST}/json-api/listaccts?api.version=1"
    response = requests.get(url, headers=HEADERS, verify=False)  
    data = response.json()
    
    if "data" in data and "acct" in data["data"]:
        return data["data"]["acct"]
    return []

# Function to get disk usage
def get_disk_usage(username):
    url = f"{CPANEL_HOST}/json-api/accountsummary?api.version=1&user={username}"
    response = requests.get(url, headers=HEADERS, verify=False)

    try:
        data = response.json()

        if "data" in data and "acct" in data["data"]:
            account_data = data["data"]["acct"][0]
            quota = account_data.get("disklimit", "N/A")  # Get quota
            used = account_data.get("diskused", "N/A")  # Get used space

            # Convert quota if not actually unlimited
            if quota.lower() == "unlimited":
                quota = "Unlimited"
                percent_used = "N/A"
            else:
                quota = int(quota.replace("M", ""))  # Convert '40960M' -> 40960
                used = int(used.replace("M", ""))  # Convert '37240M' -> 37240
                percent_used = round((used / quota) * 100, 2) if quota > 0 else "N/A"

            return quota, used, percent_used

    except Exception:
        pass  # Suppressing verbose errors

    return "N/A", "N/A", "N/A%"

# Fetch accounts and build table
accounts = get_cpanel_accounts()
table_data = []
total_quota = 0
total_used = 0

for account in accounts:
    username = account.get("user")
    domain = account.get("domain")
    quota, used, percent_used = get_disk_usage(username)

    # Sum up quota and used space (skip "Unlimited" cases)
    if isinstance(quota, int):
        total_quota += quota
    if isinstance(used, int):
        total_used += used

    table_data.append([username, domain, quota, used, f"{percent_used}%"])

# Calculate total percentage
total_percent = round((total_used / total_quota) * 100, 2) if total_quota > 0 else "N/A"

# Add total row
table_data.append(["TOTAL", "-", total_quota, total_used, f"{total_percent}%"])

# Create DataFrame and display
df = pd.DataFrame(table_data, columns=["Username", "Domain", "Quota (MB)", "Used (MB)", "Usage (%)"])
print(df)  # Print in console
df.to_csv("cpanel_accounts.csv", index=False)  # Save to CSV
print("Data saved to cpanel_accounts.csv")