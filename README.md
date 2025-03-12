# � cPanel Tools

A collection of scripts to manage and monitor **cPanel accounts** via the **cPanel API**.  
This repository includes tools for **disk usage monitoring**, **account management**, and ... !

---

## � Features
✅ **PHP Web Interface** – Displays **cPanel account disk usage** in a sortable table.  
✅ **CSV Export** – Download reports with a **single click**.  
✅ **Auto-Sorting** – Sorts by **disk usage** by default.  
✅ **Python API Script** – Fetches and processes account data **via CLI**.  
✅ **Supports Multiple cPanel Accounts** – Works with WHM API to fetch details.  

---

## � Installation

### **1️⃣ Clone the Repository**
```sh
git clone https://github.com/dahnutz/cpanel-tools.git
cd cpanel-tools
```

---

## �️ PHP Script (Web Interface)

### **� Setup**
1. **Upload the `php/disk_usage.php` script** to your cPanel or any PHP-supported web host.  
2. **Edit the script** to add your **cPanel API credentials**:
   ```php
   $cpanel_host = "https://your-cpanel-domain:2087";
   $username = "your_whm_username";
   $api_token = "your_api_token";
   ```
3. **Open the script in your browser**:  
   ```
   https://yourdomain.com/php/disk_usage.php
   ```
4. **Sort & download reports** with a click!

---

## � Python Script (CLI)

### **� Usage**
1. **Install Dependencies**:
   ```sh
   pip install requests pandas
   ```
2. **Run the script**:
   ```sh
   python python/disk_usage.py
   ```
3. **Output Example**:
   ```
   Username   Domain             Quota (MB)   Used (MB)   Usage (%)
   ---------  ----------------  ------------  ----------  ---------
   user1      example.com        10240        5123        50.1%
   user2      demo.com           5120         2048        40.0%
   .....
   TOTAL           -             100000       50000       50.00%
   Data saved to cpanel_accounts.csv
   ```
---

## � API Configuration
1. **Create a WHM API Token**:  
   - Login to WHM → Search `Manage API Tokens`
   - Click **Generate Token**
   - Copy and paste it into the script.

2. **Ensure API Permissions**:  
   - Enable access to `listaccts` & `accountsummary`.
   - Your WHM user **must have root or reseller privileges**.

---

## � License
This project is licensed under the **MIT License** – you're free to use, modify, and distribute it!  
See the [LICENSE](LICENSE) file for details.

---

## � Contributions
� **Want to improve this?** Fork the repo, make changes, and submit a **pull request**!  

---

## ⭐ Support & Feedback
If you find this useful, **star the repo** � and share it with others! �  
