import csv
from pathlib import Path

report = Path("storage/app/audit_reports/thieu_source_recheck.csv")
upload_root = Path(r"D:\Projects\HCSDLDPT\firebase_upload")

with report.open("r", encoding="utf-8-sig", newline="") as f:
    missing = [
        row for row in csv.DictReader(f)
        if row["status"] == "MISSING"
    ]

results = []

for row in missing:
    filename = row["fileName"].strip()
    matches = list(upload_root.rglob(filename))

    results.append({
        "word": row["word"],
        "fileName": filename,
        "folder_expected": row["folder_expected"],
        "status": "FOUND_IN_UPLOAD" if matches else "NOT_FOUND",
        "actual_path": str(matches[0]) if matches else "",
    })

output = Path("storage/app/audit_reports/missing_in_upload_check.csv")

with output.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=results[0].keys())
    writer.writeheader()
    writer.writerows(results)

found = sum(x["status"] == "FOUND_IN_UPLOAD" for x in results)
not_found = len(results) - found

print("TONG SO TU:", len(results))
print("TIM THAY TRONG firebase_upload:", found)
print("KHONG TIM THAY:", not_found)
print("BAO CAO:", output.resolve())