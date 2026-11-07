import csv
import os
from pathlib import Path

report = Path("storage/app/audit_reports/thieu_source_recheck.csv")
output = Path("storage/app/audit_reports/missing_drive_check.csv")

with report.open("r", encoding="utf-8-sig", newline="") as f:
    rows = [
        r for r in csv.DictReader(f)
        if r["status"] == "MISSING"
    ]

# Chỉ quét các thư mục dữ liệu có khả năng chứa WAV.
search_roots = [
    Path(r"D:\Projects"),
    Path("D:/"),
]

# Không quét lại cùng một thư mục.
found_files = {}

target_names = {r["fileName"].strip().lower() for r in rows}

for root in search_roots:
    if not root.exists():
        continue

    for current, dirs, files in os.walk(root, onerror=lambda e: None):
        dirs[:] = [
            d for d in dirs
            if d.lower() not in {
                "node_modules", ".git", "vendor",
                ".venv", "venv", "windows",
                "program files", "program files (x86)"
            }
        ]

        for filename in files:
            key = filename.lower()
            if key in target_names and key not in found_files:
                found_files[key] = str(Path(current) / filename)

results = []

for row in rows:
    filename = row["fileName"].strip()
    path = found_files.get(filename.lower())

    results.append({
        "word": row["word"],
        "fileName": filename,
        "folder_expected": row["folder_expected"],
        "status": "FOUND" if path else "NOT_FOUND",
        "actual_path": path or ""
    })

with output.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=results[0].keys())
    writer.writeheader()
    writer.writerows(results)

found = sum(r["status"] == "FOUND" for r in results)

print("Tổng số:", len(results))
print("Tìm thấy:", found)
print("Không tìm thấy:", len(results) - found)
print("Báo cáo:", output.resolve())