import csv
import requests
from pathlib import Path
from urllib.parse import quote

BASE_URL = "https://dgofnbnqpiwxqqupsxbx.supabase.co"
BUCKET = "whalespeed-audio"

input_file = Path("storage/app/audit_reports/missing_drive_check.csv")
output_file = Path("storage/app/audit_reports/missing_supabase_check.csv")

with input_file.open("r", encoding="utf-8-sig", newline="") as f:
    rows = list(csv.DictReader(f))

results = []

for i, row in enumerate(rows, 1):
    filename = row["fileName"].strip()
    folder = row["folder_expected"].strip()

    storage_folder = {
        "Ă": "A_BREVE",
        "Â": "A_CIRCUMFLEX",
        "Đ": "D_STROKE",
        "Ê": "E_CIRCUMFLEX",
        "Ô": "O_CIRCUMFLEX",
        "Ơ": "O_HORN",
        "Ư": "U_HORN",
    }.get(folder, folder)

    storage_path = f"{storage_folder}/{filename}"

    encoded_path = "/".join(
        quote(part, safe="") for part in storage_path.split("/")
    )

    url = f"{BASE_URL}/storage/v1/object/public/{BUCKET}/{encoded_path}"

    try:
        response = requests.get(url, timeout=30)

        if response.status_code == 200:
            status = "FOUND"
        elif response.status_code == 404:
            status = "NOT_FOUND"
        else:
            status = f"ERROR_{response.status_code}"
            print("CHI TIẾT LỖI:", response.text[:500])

    except requests.RequestException as e:
        status = "CONNECTION_ERROR"

    results.append({
        "word": row["word"],
        "fileName": filename,
        "storagePath": storage_path,
        "status": status,
    })

    print(f"{i}/50: {filename} - {status}")

with output_file.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=results[0].keys())
    writer.writeheader()
    writer.writerows(results)

from collections import Counter
counts = Counter(r["status"] for r in results)

print("\nKẾT QUẢ:")
for status, count in counts.items():
    print(status, ":", count)

print("Báo cáo:", output_file.resolve())
