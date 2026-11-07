import csv
from pathlib import Path

input_file = Path("storage/app/audit_reports/thieu_source_audit.csv")
output_file = Path("storage/app/audit_reports/thieu_source_recheck.csv")

source_root = Path(r"D:\Projects\HCSDLDPT\source")

with input_file.open("r", encoding="utf-8-sig", newline="") as f:
    rows = list(csv.DictReader(f))

results = []

for row in rows:
    filename = row["fileName"].strip()
    folder = row["folder_expected"].strip()

    expected_path = source_root / folder / filename

    # Kiểm tra thêm toàn bộ thư mục nếu không thấy ở vị trí dự kiến.
    matches = list(source_root.rglob(filename))

    if expected_path.is_file():
        status = "FOUND_EXPECTED"
        actual_path = str(expected_path)
    elif matches:
        status = "FOUND_OTHER_FOLDER"
        actual_path = str(matches[0])
    else:
        status = "MISSING"
        actual_path = ""

    results.append({
        "word": row["word"],
        "fileName": filename,
        "folder_expected": folder,
        "status": status,
        "actual_path": actual_path,
    })

with output_file.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=results[0].keys())
    writer.writeheader()
    writer.writerows(results)

from collections import Counter
counts = Counter(x["status"] for x in results)

print("TONG SO TU:", len(results))
for status, count in counts.items():
    print(status, ":", count)

print("BAO CAO:", output_file.resolve())
