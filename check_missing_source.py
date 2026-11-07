import csv
from pathlib import Path
from collections import Counter

SOURCE_DIR = Path(r"D:\Projects\HCSDLDPT\source")

INPUT_FILE = Path("storage/app/audit_reports/missing_supabase_check.csv")
OUTPUT_FILE = Path("storage/app/audit_reports/50_source_recheck.csv")

with INPUT_FILE.open("r", encoding="utf-8-sig", newline="") as f:
    rows = list(csv.DictReader(f))

results = []

for row in rows:
    word = row["word"].strip()
    filename = row["fileName"].strip()
    folder = row["storagePath"].split("/")[0]

    file_path = SOURCE_DIR / folder / filename

    if file_path.is_file():
        status = "FOUND"
    else:
        status = "MISSING"

    results.append({
        "word": word,
        "fileName": filename,
        "folder": folder,
        "source_path": str(file_path),
        "status": status
    })

    print(f"{filename}: {status}")

with OUTPUT_FILE.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=results[0].keys())
    writer.writeheader()
    writer.writerows(results)

counts = Counter(row["status"] for row in results)

print("\n===== KẾT QUẢ =====")
print("Tìm thấy:", counts["FOUND"])
print("Còn thiếu:", counts["MISSING"])
print("Báo cáo:", OUTPUT_FILE.resolve())