import csv
import unicodedata
from pathlib import Path

missing_file = Path("storage/app/audit_reports/missing_drive_check.csv")
excel_file = Path("storage/app/audit_reports/excel_unique_pairs.csv")
output_file = Path("storage/app/audit_reports/missing_excel_comparison.csv")

def normalize(value):
    return unicodedata.normalize("NFC", str(value).strip()).casefold()

with missing_file.open("r", encoding="utf-8-sig", newline="") as f:
    missing = list(csv.DictReader(f))

with excel_file.open("r", encoding="utf-8-sig", newline="") as f:
    excel = list(csv.DictReader(f))

excel_pairs = {
    (normalize(r["word"]), normalize(r["fileName"])): r
    for r in excel
}

results = []

for row in missing:
    key = (normalize(row["word"]), normalize(row["fileName"]))
    matched = excel_pairs.get(key)

    results.append({
        "word": row["word"],
        "fileName": row["fileName"],
        "in_excel": "YES" if matched else "NO",
        "excel_sheet": matched["sheet"] if matched else ""
    })

with output_file.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=results[0].keys())
    writer.writeheader()
    writer.writerows(results)

yes = sum(r["in_excel"] == "YES" for r in results)

print("Tổng số:", len(results))
print("Có trong Excel:", yes)
print("Không có trong Excel:", len(results) - yes)
print("Báo cáo:", output_file.resolve())