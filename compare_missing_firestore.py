import csv
import json
import unicodedata
from pathlib import Path

missing_file = Path("storage/app/audit_reports/missing_drive_check.csv")
firestore_file = Path("storage/app/firestore_words_audit.json")
output_file = Path("storage/app/audit_reports/missing_firestore_comparison.csv")

def normalize(value):
    return unicodedata.normalize("NFC", str(value).strip()).casefold()

with missing_file.open("r", encoding="utf-8-sig", newline="") as f:
    missing = list(csv.DictReader(f))

with firestore_file.open("r", encoding="utf-8") as f:
    firestore = json.load(f)

lookup = {}

for doc in firestore:
    key = (normalize(doc["word"]), normalize(doc["fileName"]))
    lookup.setdefault(key, []).append(doc)

results = []

for row in missing:
    key = (normalize(row["word"]), normalize(row["fileName"]))
    matches = lookup.get(key, [])

    if matches:
        for doc in matches:
            results.append({
                "word": row["word"],
                "fileName": row["fileName"],
                "firestore_status": "FOUND",
                "documentId": doc["id"],
                "storagePath": doc["storagePath"]
            })
    else:
        results.append({
            "word": row["word"],
            "fileName": row["fileName"],
            "firestore_status": "NOT_FOUND",
            "documentId": "",
            "storagePath": ""
        })

with output_file.open("w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=results[0].keys())
    writer.writeheader()
    writer.writerows(results)

found = sum(r["firestore_status"] == "FOUND" for r in results)
not_found = sum(r["firestore_status"] == "NOT_FOUND" for r in results)

print("Tổng số:", len(missing))
print("Có trong Firestore:", found)
print("Không có trong Firestore:", not_found)
print("Báo cáo:", output_file.resolve())
